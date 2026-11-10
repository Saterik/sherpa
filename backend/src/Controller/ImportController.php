<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\HttpException;
use App\Message\ImportMessage;
use App\Service\ImportService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Messenger\MessageBusInterface;

class ImportController
{
    public function __construct(
        private ImportService $importService,
        private MessageBusInterface $messageBus
    ) {}

    public function import(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'] ?? null;
        
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            throw HttpException::badRequest('No file uploaded or upload error');
        }

        // Save uploaded file temporarily
        $tempPath = sys_get_temp_dir() . '/import_' . uniqid() . '.xlsx';
        $file->moveTo($tempPath);

        // Generate job ID
        $jobId = 'import_' . uniqid();
        
        // Dispatch async job
        $message = new ImportMessage(
            jobId: $jobId,
            filePath: $tempPath,
            userId: $request->getAttribute('user_id') ?? 'anonymous'
        );
        
        $this->messageBus->dispatch($message);

        $response->getBody()->write(json_encode([
            'job_id' => $jobId,
            'status' => 'queued',
            'message' => 'Import job queued for processing',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $response
            ->withStatus(202)
            ->withHeader('Content-Type', 'application/json');
    }

    public function status(Request $request, Response $response, array $args): Response
    {
        $jobId = $args['jobId'] ?? '';
        
        // In a real implementation, you would check job status from a database/Redis
        // For now, return a mock response
        $response->getBody()->write(json_encode([
            'job_id' => $jobId,
            'status' => 'processing',
            'progress' => 50,
            'message' => 'Import in progress',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function report(Request $request, Response $response, array $args): Response
    {
        $jobId = $args['jobId'] ?? '';
        
        // In a real implementation, you would fetch the report from storage
        $response->getBody()->write(json_encode([
            'job_id' => $jobId,
            'errors' => [],
            'stats' => [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'failed' => 0,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}