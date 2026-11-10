<?php

declare(strict_types=1);

use App\Controller\ProductController;
use App\Controller\ImportController;
use App\Controller\HealthController;
use App\Controller\AuthController;
use App\Middleware\JwtAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    error_log("ROUTES.PHP EXECUTED - START");
    
    // Health check (no auth required)
    $app->get('/api/health', [HealthController::class, 'check'])
        ->setName('health');
    error_log("Routes after health: " . count($app->getRouteCollector()->getRoutes()));

    // Auth routes (no auth required) - UNDER /api/auth
    $app->group('/api/auth', function (RouteCollectorProxy $group) {
        $group->post('/login', [AuthController::class, 'login'])
            ->setName('auth.login');
        $group->post('/register', [AuthController::class, 'register'])
            ->setName('auth.register');
        $group->post('/refresh', [AuthController::class, 'refresh'])
            ->setName('auth.refresh');
        
        // OPTIONS handlers for CORS preflight
        $group->map(['OPTIONS'], '/login', function ($request, $response) {
            return $response->withStatus(204);
        });
        $group->map(['OPTIONS'], '/register', function ($request, $response) {
            return $response->withStatus(204);
        });
        $group->map(['OPTIONS'], '/refresh', function ($request, $response) {
            return $response->withStatus(204);
        });
    });
    error_log("Routes after auth group: " . count($app->getRouteCollector()->getRoutes()));

    // Protected routes (JWT required) - apply JWT middleware ONLY to this group
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Product routes
        $group->get('/products', [ProductController::class, 'list'])
            ->setName('products.list');
        $group->get('/products/{id}', [ProductController::class, 'get'])
            ->setName('products.get');
        $group->post('/products', [ProductController::class, 'create'])
            ->setName('products.create');
        $group->put('/products/{id}', [ProductController::class, 'update'])
            ->setName('products.update');
        $group->delete('/products/{id}', [ProductController::class, 'delete'])
            ->setName('products.delete');

        // Import routes
        $group->post('/import', [ImportController::class, 'import'])
            ->setName('import');
        $group->get('/import/{jobId}/status', [ImportController::class, 'status'])
            ->setName('import.status');
        $group->get('/import/{jobId}/report', [ImportController::class, 'report'])
            ->setName('import.report');
    })->add(new JwtAuthMiddleware()); // JWT middleware ONLY for /api/* routes
    
    error_log("Routes after all: " . count($app->getRouteCollector()->getRoutes()));
    error_log("ROUTES.PHP EXECUTED - END");
};