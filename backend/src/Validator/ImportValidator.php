<?php

declare(strict_types=1);

namespace App\Validator;

class ImportValidator
{
    public function __construct(
        private int $maxFileSize,
        private array $allowedMimeTypes
    ) {}

    public function validate(\Slim\Psr7\UploadedFile $file): array
    {
        $errors = [];

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            $errors[] = sprintf(
                'File size exceeds maximum allowed size of %d bytes',
                $this->maxFileSize
            );
        }

        // Check MIME type
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
            $errors[] = sprintf(
                'Invalid file type: %s. Allowed types: %s',
                $mimeType,
                implode(', ', $this->allowedMimeTypes)
            );
        }

        // Check file extension
        $filename = $file->getClientFilename();
        if ($filename) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($extension !== 'xlsx') {
                $errors[] = 'File must have .xlsx extension';
            }
        }

        return $errors;
    }

    public function validateParsedData(array $data): array
    {
        $errors = [];

        foreach ($data as $index => $row) {
            $rowErrors = [];

            if (empty($row['external_code'])) {
                $rowErrors[] = 'Missing external_code';
            }

            if (empty($row['name'])) {
                $rowErrors[] = 'Missing name';
            }

            if (!isset($row['price']) || $row['price'] === '') {
                $rowErrors[] = 'Missing price';
            } elseif (!is_numeric($row['price'])) {
                $rowErrors[] = 'Invalid price format';
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $index + 1,
                    'external_code' => $row['external_code'] ?? 'unknown',
                    'errors' => $rowErrors,
                ];
            }
        }

        return $errors;
    }
}