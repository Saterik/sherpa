<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthMiddleware
{
    // Public routes that don't require authentication
    private array $publicRoutes = [
        '/api/health',
        '/api/auth/login',
        '/api/auth/register',
        '/api/auth/refresh',
    ];

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $path = $request->getUri()->getPath();
        
        // Skip authentication for public routes
        if (in_array($path, $this->publicRoutes, true)) {
            return $handler->handle($request);
        }
        
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            throw HttpException::unauthorized('Authorization header is required');
        }

        if (!preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw HttpException::unauthorized('Invalid authorization header format');
        }

        $token = $matches[1];
        $secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            
            // Add user info to request attributes
            $request = $request->withAttribute('user', $decoded);
            $request = $request->withAttribute('user_id', $decoded->sub ?? null);
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw HttpException::unauthorized('Token has expired');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw HttpException::unauthorized('Invalid token signature');
        } catch (\Firebase\JWT\BeforeValidException $e) {
            throw HttpException::unauthorized('Token not yet valid');
        } catch (\Throwable $e) {
            throw HttpException::unauthorized('Invalid token: ' . $e->getMessage());
        }

        return $handler->handle($request);
    }
}