<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HealthController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function check(Request $request, Response $response): Response
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ];
        
        $status = $checks['database'] ? 'healthy' : 'unhealthy';
        $httpStatus = $checks['database'] ? 200 : 503;
        
        $payload = [
            'status' => $status,
            'checks' => $checks,
        ];

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withStatus($httpStatus)
            ->withHeader('Content-Type', 'application/json');
    }

    private function checkDatabase(): bool
    {
        try {
            $conn = $this->entityManager->getConnection();
            // In DBAL 4.x, just execute a simple query to test connection
            $result = $conn->executeQuery('SELECT 1');
            return $result->fetchOne() === 1;
        } catch (\Throwable) {
            return false;
        }
    }
}