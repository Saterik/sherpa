<?php

declare(strict_types=1);

use App\Controller\ProductController;
use App\Controller\ImportController;
use App\Controller\HealthController;
use App\Controller\AuthController;
use App\Repository\ProductRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductImageRepository;
use App\Service\ProductService;
use App\Service\ImportService;
use App\Service\ImageDownloadService;
use App\Service\XlsxParserService;
use App\Validator\ImportValidator;
use App\Message\ImportMessageHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;
use GuzzleHttp\Client;
use Tuupola\Middleware\JwtAuthentication;
use Tuupola\Middleware\RateLimit;

return [
    // Database Configuration
    'settings' => [
        'db' => [
            'driver' => $_ENV['DATABASE_DRIVER'] ?? 'pdo_pgsql',
            'host' => $_ENV['DATABASE_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['DATABASE_PORT'] ?? 5432),
            'dbname' => $_ENV['DATABASE_NAME'] ?? 'sherpa',
            'user' => $_ENV['DATABASE_USER'] ?? 'sherpa',
            'password' => $_ENV['DATABASE_PASSWORD'] ?? 'sherpa_password',
            'charset' => $_ENV['DATABASE_CHARSET'] ?? 'utf8',
        ],
    ],

    // Doctrine EntityManager
    EntityManagerInterface::class => function ($container) {
        $settings = $container->get('settings')['db'];
        $connection = DriverManager::getConnection($settings);

        // Register UUID type
        \Doctrine\DBAL\Types\Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('uuid', 'uuid');

        $config = new Configuration();
        $config->setProxyDir(__DIR__ . '/../var/cache/doctrine/proxies');
        $config->setProxyNamespace('App\Proxy');
        $config->setAutoGenerateProxyClasses(true);

        // Metadata driver (attributes) - Doctrine 3.x uses AttributeDriver
        $driver = new \Doctrine\ORM\Mapping\Driver\AttributeDriver(
            [__DIR__ . '/../src/Entity']
        );
        $config->setMetadataDriverImpl($driver);

        return new \Doctrine\ORM\EntityManager($connection, $config);
    },

    // Repositories
    ProductRepository::class => function ($container) {
        return $container->get(EntityManagerInterface::class)->getRepository(\App\Entity\Product::class);
    },
    ProductAttributeRepository::class => function ($container) {
        return $container->get(EntityManagerInterface::class)->getRepository(\App\Entity\ProductAttribute::class);
    },
    ProductImageRepository::class => function ($container) {
        return $container->get(EntityManagerInterface::class)->getRepository(\App\Entity\ProductImage::class);
    },

    // Services
    ProductService::class => function ($container) {
        return new ProductService(
            $container->get(ProductRepository::class),
            $container->get(ProductAttributeRepository::class),
            $container->get(ProductImageRepository::class),
            $container->get(EntityManagerInterface::class)
        );
    },

    ImportService::class => function ($container) {
        return new ImportService(
            $container->get(ProductService::class),
            $container->get(XlsxParserService::class),
            $container->get(ImageDownloadService::class),
            $container->get(ImportValidator::class),
            $container->get(EntityManagerInterface::class)
        );
    },

    ImageDownloadService::class => function ($container) {
        return new ImageDownloadService(
            new Client(['timeout' => 30]),
            $_ENV['IMAGE_STORAGE_PATH'] ?? '/var/www/html/storage/images',
            $_ENV['IMAGE_PUBLIC_PATH'] ?? '/storage/images'
        );
    },

    XlsxParserService::class => function ($container) {
        return new XlsxParserService();
    },

    ImportValidator::class => function ($container) {
        return new ImportValidator(
            (int)($_ENV['MAX_FILE_SIZE'] ?? 10485760),
            explode(',', $_ENV['ALLOWED_MIME_TYPES'] ?? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        );
    },

    // Message Bus for async processing (simplified - synchronous processing)
    MessageBusInterface::class => function ($container) {
        $handler = new ImportMessageHandler($container->get(ImportService::class));
        $handlersLocator = new HandlersLocator([
            'App\Message\ImportMessage' => [$handler],
        ]);

        $bus = new MessageBus([
            new AddBusNameStampMiddleware('command.bus'),
            new HandleMessageMiddleware($handlersLocator),
        ]);
        
        return $bus;
    },

    // Controllers
    ProductController::class => function ($container) {
        return new ProductController($container->get(ProductService::class));
    },

    ImportController::class => function ($container) {
        return new ImportController(
            $container->get(ImportService::class),
            $container->get(MessageBusInterface::class)
        );
    },

    HealthController::class => function ($container) {
        return new HealthController($container->get(EntityManagerInterface::class));
    },

    AuthController::class => function ($container) {
        return new AuthController($container->get(EntityManagerInterface::class));
    },

    // HTTP Client
    Client::class => function () {
        return new Client(['timeout' => 30]);
    },

    // Middleware
    JwtAuthentication::class => function () {
        return new JwtAuthentication([
            'secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key',
            'algorithm' => ['HS256'],
            'header' => 'Authorization',
            'regexp' => '/^Bearer\s+(.*)$/i',
            'secure' => false,
            'error' => function ($response, $arguments) {
                $data = ['error' => 'Unauthorized', 'message' => $arguments['message']];
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(401)
                    ->withBody(new \Slim\Psr7\Factory\StreamFactory()->createStream(json_encode($data)));
            },
        ]);
    },

    RateLimit::class => function () {
        return new RateLimit([
            'limit' => (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 100),
            'window' => (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 60),
            'key' => function ($request) {
                return $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
            },
            'error' => function ($response) {
                $data = ['error' => 'Too Many Requests', 'message' => 'Rate limit exceeded'];
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(429)
                    ->withBody(new \Slim\Psr7\Factory\StreamFactory()->createStream(json_encode($data)));
            },
        ]);
    },

    App\Middleware\CorsMiddleware::class => function () {
        return new \App\Middleware\CorsMiddleware();
    },

    App\Middleware\ErrorHandlerMiddleware::class => function ($container) {
        $logger = null;
        if ($container->has(\Psr\Log\LoggerInterface::class)) {
            $logger = $container->get(\Psr\Log\LoggerInterface::class);
        }
        return new \App\Middleware\ErrorHandlerMiddleware($logger);
    },
];