<?php

declare(strict_types=1);

use App\Exception\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Instantiate App
AppFactory::setContainer($container = new \DI\Container());
$app = AppFactory::create();

// Add routing middleware
$app->addRoutingMiddleware();

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware(
    (bool) $_ENV['APP_DEBUG'],
    true,
    true
);

// Custom error handler for JSON responses
$errorMiddleware->setDefaultErrorHandler(
    function (Request $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($app): Response {
        $response = $app->getResponseFactory()->createResponse();
        $payload = [
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ],
        ];

        if ($exception instanceof HttpException) {
            $response = $response->withStatus($exception->getCode());
        } else {
            $response = $response->withStatus(500);
        }

        if ($displayErrorDetails) {
            $payload['error']['trace'] = $exception->getTraceAsString();
            $payload['error']['file'] = $exception->getFile();
            $payload['error']['line'] = $exception->getLine();
        }

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json');
    }
);

// Register dependencies
require __DIR__ . '/dependencies.php';

// Register middleware
require __DIR__ . '/middleware.php';

// Register routes
require __DIR__ . '/routes.php';

return $app;