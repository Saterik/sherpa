<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\ProductImage;
use App\Repository\ProductRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductImageRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductAttributeRepository $attributeRepository,
        private ProductImageRepository $imageRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getPaginated(
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
        ?float $priceMin = null,
        ?float $priceMax = null
    ): array {
        return $this->productRepository->findPaginated($page, $limit, $search, $priceMin, $priceMax);
    }

    public function getById(string $id): ?Product
    {
        return $this->productRepository->findWithRelations($id);
    }

    public function getByExternalCode(string $externalCode): ?Product
    {
        return $this->productRepository->findByExternalCode($externalCode);
    }

    public function create(array $data): Product
    {
        $product = new Product();
        $this->populateProduct($product, $data);
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $this->populateProduct($product, $data);
        $product->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();
        
        return $product;
    }

    public function delete(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    public function upsert(array $data): Product
    {
        $externalCode = $data['external_code'] ?? '';
        $existing = $this->getByExternalCode($externalCode);
        
        if ($existing) {
            return $this->update($existing, $data);
        }
        
        return $this->create($data);
    }

    public function bulkUpsert(array $productsData): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        foreach ($productsData as $index => $data) {
            try {
                $externalCode = $data['external_code'] ?? '';
                $existing = $this->getByExternalCode($externalCode);
                
                if ($existing) {
                    $this->update($existing, $data);
                    $results['updated']++;
                } else {
                    $this->create($data);
                    $results['created']++;
                }
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'row' => $index + 1,
                    'external_code' => $data['external_code'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function populateProduct(Product $product, array $data): void
    {
        if (isset($data['external_code'])) {
            $product->setExternalCode($data['external_code']);
        }
        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['discount'])) {
            $product->setDiscount($data['discount']);
        }
        if (isset($data['currency'])) {
            $product->setCurrency($data['currency']);
        }
        if (isset($data['sku'])) {
            $product->setSku($data['sku']);
        }
        if (isset($data['unit'])) {
            $product->setUnit($data['unit']);
        }
        if (isset($data['country'])) {
            $product->setCountry($data['country']);
        }
        if (isset($data['vat'])) {
            $product->setVat($data['vat']);
        }
        if (isset($data['brand'])) {
            $product->setBrand($data['brand']);
        }
        if (isset($data['barcode'])) {
            $product->setBarcode($data['barcode']);
        }
        if (isset($data['is_archived'])) {
            $product->setIsArchived($data['is_archived']);
        }
        if (isset($data['weight'])) {
            $product->setWeight($data['weight']);
        }
        if (isset($data['is_weight_product'])) {
            $product->setIsWeightProduct($data['is_weight_product']);
        }
        if (isset($data['is_marked'])) {
            $product->setIsMarked($data['is_marked']);
        }
        if (isset($data['volume'])) {
            $product->setVolume($data['volume']);
        }
        if (isset($data['has_excise_mark'])) {
            $product->setHasExciseMark($data['has_excise_mark']);
        }
        if (isset($data['category'])) {
            $product->setCategory($data['category']);
        }
        if (isset($data['seo_title'])) {
            $product->setSeoTitle($data['seo_title']);
        }
        if (isset($data['seo_h1'])) {
            $product->setSeoH1($data['seo_h1']);
        }
        if (isset($data['seo_description'])) {
            $product->setSeoDescription($data['seo_description']);
        }
        if (isset($data['package_quantity'])) {
            $product->setPackageQuantity($data['package_quantity']);
        }
        if (isset($data['package_url'])) {
            $product->setPackageUrl($data['package_url']);
        }
        if (isset($data['packaging_url'])) {
            $product->setPackagingUrl($data['packaging_url']);
        }
        if (isset($data['product_weight'])) {
            $product->setProductWeight($data['product_weight']);
        }
        if (isset($data['width'])) {
            $product->setWidth($data['width']);
        }
        if (isset($data['height'])) {
            $product->setHeight($data['height']);
        }
        if (isset($data['length'])) {
            $product->setLength($data['length']);
        }
        if (isset($data['package_weight'])) {
            $product->setPackageWeight($data['package_weight']);
        }
        if (isset($data['package_width'])) {
            $product->setPackageWidth($data['package_width']);
        }
        if (isset($data['package_height'])) {
            $product->setPackageHeight($data['package_height']);
        }
        if (isset($data['package_length'])) {
            $product->setPackageLength($data['package_length']);
        }

        // Handle attributes
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            // Remove existing attributes not in new data
            $existingKeys = [];
            foreach ($product->getAttributes() as $attr) {
                $existingKeys[$attr->getKey()] = $attr;
            }

            foreach ($data['attributes'] as $key => $value) {
                if (isset($existingKeys[$key])) {
                    $existingKeys[$key]->setValue((string)$value);
                    unset($existingKeys[$key]);
                } else {
                    $attribute = new ProductAttribute();
                    $attribute->setKey($key);
                    $attribute->setValue((string)$value);
                    $product->addAttribute($attribute);
                }
            }

            // Remove attributes not in new data
            foreach ($existingKeys as $attr) {
                $product->removeAttribute($attr);
                $this->entityManager->remove($attr);
            }
        }

        // Handle images
        if (isset($data['images']) && is_array($data['images'])) {
            // Clear existing images
            foreach ($product->getImages() as $image) {
                $product->removeImage($image);
                $this->entityManager->remove($image);
            }

            foreach ($data['images'] as $index => $imageData) {
                $image = new ProductImage();
                $image->setUrl($imageData['url'] ?? '');
                $image->setPath($imageData['path'] ?? null);
                $image->setFilename($imageData['filename'] ?? null);
                $image->setSize($imageData['size'] ?? null);
                $image->setMimeType($imageData['mime_type'] ?? null);
                $image->setIsMain($imageData['is_main'] ?? ($index === 0));
                $image->setSortOrder($imageData['sort_order'] ?? $index);
                $image->setDownloadStatus($imageData['download_status'] ?? 'pending');
                $image->setDownloadError($imageData['download_error'] ?? null);
                if (isset($imageData['downloaded_at'])) {
                    $image->setDownloadedAt(new \DateTimeImmutable($imageData['downloaded_at']));
                }
                $product->addImage($image);
            }
        }
    }
}