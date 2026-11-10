<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware
{
    public function __construct()
    {
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        error_log("CorsMiddleware INVOKE: " . $request->getMethod() . " " . $request->getUri()->getPath());
        
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigins = explode(',', $_ENV['FRONTEND_URL'] ?? 'http://localhost:4200');
        
        $isAllowed = in_array($origin, $allowedOrigins, true);
        
        // Handle preflight requests FIRST - before calling handler
        if ($request->getMethod() === 'OPTIONS') {
            error_log("CorsMiddleware: Handling OPTIONS request for " . $request->getUri()->getPath());
            $response = new \Slim\Psr7\Response();
            if ($isAllowed) {
                $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
            $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response = $response->withHeader('Access-Control-Max-Age', '86400');
            error_log("CorsMiddleware: Returning 204 for OPTIONS");
            return $response->withStatus(204);
        }
        
        error_log("CorsMiddleware: Calling handler for " . $request->getMethod() . " " . $request->getUri()->getPath());
        $response = $handler->handle($request);
        
        if ($isAllowed) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response = $response->withHeader('Access-Control-Max-Age', '86400');
        
        return $response;
    }
}