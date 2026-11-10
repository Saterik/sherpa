<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ImageDownloadService
{
    public function __construct(
        private Client $client,
        private string $storagePath,
        private string $publicPath
    ) {
        // Create storage directory if it doesn't exist
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function download(string $url): array
    {
        $result = [
            'url' => $url,
            'path' => null,
            'filename' => null,
            'size' => null,
            'mime_type' => null,
            'error' => null,
        ];

        try {
            $response = $this->client->get($url, [
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $content = $response->getBody()->getContents();
            $mimeType = $response->getHeaderLine('Content-Type');
            $size = strlen($content);

            // Generate filename from URL
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';
            $filename = basename($path);
            
            // If no extension, try to get from mime type
            if ($filename && !pathinfo($filename, PATHINFO_EXTENSION)) {
                $ext = $this->getExtensionFromMime($mimeType);
                if ($ext) {
                    $filename .= '.' . $ext;
                }
            }
            
            // Generate unique filename to avoid conflicts
            $uniqueFilename = uniqid('img_', true) . '_' . $filename;
            $filePath = $this->storagePath . '/' . $uniqueFilename;
            
            // Save file
            file_put_contents($filePath, $content);
            
            $result['path'] = $this->publicPath . '/' . $uniqueFilename;
            $result['filename'] = $uniqueFilename;
            $result['size'] = $size;
            $result['mime_type'] = $mimeType;
            
        } catch (GuzzleException $e) {
            $result['error'] = $e->getMessage();
        } catch (\Throwable $e) {
            $result['error'] = 'File save error: ' . $e->getMessage();
        }

        return $result;
    }

    public function downloadMultiple(array $urls): array
    {
        $results = [];
        foreach ($urls as $url) {
            $results[] = $this->download($url);
        }
        return $results;
    }

    private function getExtensionFromMime(string $mimeType): ?string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
            'image/svg+xml' => 'svg',
        ];
        
        return $map[$mimeType] ?? null;
    }
}