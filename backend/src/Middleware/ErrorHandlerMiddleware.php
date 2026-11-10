<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorHandlerMiddleware
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {
        error_log("ErrorHandlerMiddleware CONSTRUCTED");
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (HttpException $e) {
            $this->logger?->warning('HTTP Exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'url' => (string)$request->getUri(),
            ]);
            
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            
            return $response
                ->withStatus($e->getCode())
                ->withHeader('Content-Type', 'application/json');
        } catch (Throwable $e) {
            $this->logger?->error('Unhandled Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => (string)$request->getUri(),
            ]);
            
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => [
                    'message' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal server error',
                    'code' => 500,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}