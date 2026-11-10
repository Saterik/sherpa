<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Validator\ImportValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ImportService
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private ProductService $productService,
        private XlsxParserService $parserService,
        private ImageDownloadService $imageDownloadService,
        private ImportValidator $validator,
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null
    ) {}

    public function import(string $filePath): array
    {
        // Parse the XLSX file
        $parsedData = $this->parserService->parse($filePath);
        
        // Validate parsed data
        $validationErrors = $this->validator->validateParsedData($parsedData);
        
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors,
                'stats' => [
                    'total' => count($parsedData),
                    'created' => 0,
                    'updated' => 0,
                    'failed' => count($validationErrors),
                ],
            ];
        }

        // Process in batches
        $results = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $batches = array_chunk($parsedData, self::BATCH_SIZE);
        
        foreach ($batches as $batchIndex => $batch) {
            $this->entityManager->beginTransaction();
            
            try {
                foreach ($batch as $rowIndex => $rowData) {
                    $absoluteIndex = $batchIndex * self::BATCH_SIZE + $rowIndex;
                    
                    try {
                        // Download images first
                        if (!empty($rowData['images'])) {
                            $imageResults = $this->downloadImages($rowData['images']);
                            $rowData['images'] = $imageResults;
                        }

                        // Upsert product
                        $existing = $this->productService->getByExternalCode($rowData['external_code']);
                        $this->productService->upsert($rowData);
                        
                        if ($existing) {
                            $results['updated']++;
                        } else {
                            $results['created']++;
                        }
                    } catch (\Throwable $e) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $absoluteIndex + 1,
                            'external_code' => $rowData['external_code'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ];
                        $this->logger?->error('Import row failed', [
                            'row' => $absoluteIndex + 1,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                $this->entityManager->commit();
            } catch (\Throwable $e) {
                $this->entityManager->rollBack();
                $this->logger?->error('Batch import failed', [
                    'batch' => $batchIndex,
                    'error' => $e->getMessage(),
                ]);
                
                // Mark all rows in this batch as failed
                foreach ($batch as $rowIndex => $rowData) {
                    $absoluteIndex = $batchIndex * self::BATCH_SIZE + $rowIndex;
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $absoluteIndex + 1,
                        'external_code' => $rowData['external_code'] ?? 'unknown',
                        'error' => 'Batch transaction failed: ' . $e->getMessage(),
                    ];
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Import completed',
            'stats' => [
                'total' => count($parsedData),
                'created' => $results['created'],
                'updated' => $results['updated'],
                'failed' => $results['failed'],
            ],
            'errors' => $results['errors'],
        ];
    }

    public function importAsync(string $filePath, string $jobId): void
    {
        // This method is called by the message handler
        // The actual import logic is the same, but we could add job tracking here
        $this->import($filePath);
    }

    private function downloadImages(array $images): array
    {
        $results = [];
        
        foreach ($images as $imageData) {
            $url = $imageData['url'] ?? '';
            if (empty($url)) {
                continue;
            }
            
            $downloadResult = $this->imageDownloadService->download($url);
            
            $results[] = [
                'url' => $url,
                'path' => $downloadResult['path'],
                'filename' => $downloadResult['filename'],
                'size' => $downloadResult['size'],
                'mime_type' => $downloadResult['mime_type'],
                'is_main' => $imageData['is_main'] ?? false,
                'sort_order' => $imageData['sort_order'] ?? 0,
                'download_status' => $downloadResult['error'] ? 'failed' : 'downloaded',
                'download_error' => $downloadResult['error'],
                'downloaded_at' => $downloadResult['error'] ? null : (new \DateTimeImmutable())->format('c'),
            ];
        }
        
        return $results;
    }
}