<?php

declare(strict_types=1);

namespace App\Message;

use App\Service\ImportService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportMessageHandler
{
    public function __construct(
        private ImportService $importService,
        private ?LoggerInterface $logger = null
    ) {}

    public function __invoke(ImportMessage $message): void
    {
        $this->logger?->info('Starting async import job', [
            'job_id' => $message->jobId,
            'file' => $message->filePath,
        ]);

        try {
            $result = $this->importService->import($message->filePath);
            
            $this->logger?->info('Async import job completed', [
                'job_id' => $message->jobId,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            $this->logger?->error('Async import job failed', [
                'job_id' => $message->jobId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}