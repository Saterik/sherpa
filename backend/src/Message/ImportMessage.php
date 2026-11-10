<?php

declare(strict_types=1);

namespace App\Message;

class ImportMessage
{
    public function __construct(
        public readonly string $jobId,
        public readonly string $filePath,
        public readonly string $userId,
        public readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {}
}