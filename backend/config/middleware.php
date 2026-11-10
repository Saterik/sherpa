<?php

declare(strict_types=1);

use App\Middleware\CorsMiddleware;
use App\Middleware\ErrorHandlerMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

error_log("MIDDLEWARE.PHP EXECUTED");

return function (App $app) {
    error_log("MIDDLEWARE.PHP FUNCTION CALLED");
    
    // CORS Middleware (should be first)
    error_log("Adding CorsMiddleware");
    $app->add(CorsMiddleware::class);
    error_log("CorsMiddleware added");
    
    // Error Handler Middleware
    error_log("Adding ErrorHandlerMiddleware");
    $app->add(ErrorHandlerMiddleware::class);
    error_log("ErrorHandlerMiddleware added");
    
    // Global middleware can be added here
    // $app->add(JwtAuthentication::class); // Applied per route group
    // $app->add(RateLimit::class); // Applied per route group
};