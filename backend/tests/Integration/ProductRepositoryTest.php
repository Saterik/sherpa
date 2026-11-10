<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\ProductImage;
use App\Repository\ProductRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ProductRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;
    private ProductAttributeRepository $attributeRepository;
    private ProductImageRepository $imageRepository;

    protected function setUp(): void
    {
        // This would need a real database connection for integration tests
        // For now, we'll mark this as skipped
        $this->markTestSkipped('Integration tests require database setup');
    }

    public function testFindByExternalCode(): void
    {
        $externalCode = 'test-code-123';
        $product = $this->productRepository->findByExternalCode($externalCode);
        
        $this->assertNull($product);
    }

    public function testFindPaginated(): void
    {
        $result = $this->productRepository->findPaginated(1, 10);
        
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertArrayHasKey('total_pages', $result);
    }

    public function testProductCrud(): void
    {
        // Create
        $product = new Product();
        $product->setExternalCode('test-new-123');
        $product->setName('Test Product');
        $product->setPrice('100.00');
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        $this->assertNotNull($product->getId());
        
        // Read
        $found = $this->productRepository->findByExternalCode('test-new-123');
        $this->assertNotNull($found);
        $this->assertEquals('Test Product', $found->getName());
        
        // Update
        $found->setName('Updated Product');
        $this->entityManager->flush();
        
        $updated = $this->productRepository->findByExternalCode('test-new-123');
        $this->assertEquals('Updated Product', $updated->getName());
        
        // Delete
        $this->entityManager->remove($updated);
        $this->entityManager->flush();
        
        $deleted = $this->productRepository->findByExternalCode('test-new-123');
        $this->assertNull($deleted);
    }

    public function testAttributesRelationship(): void
    {
        $product = new Product();
        $product->setExternalCode('test-attr-123');
        $product->setName('Product with Attributes');
        $product->setPrice('50.00');
        
        $attr1 = new ProductAttribute();
        $attr1->setKey('Color');
        $attr1->setValue('Red');
        $product->addAttribute($attr1);
        
        $attr2 = new ProductAttribute();
        $attr2->setKey('Size');
        $attr2->setValue('M');
        $product->addAttribute($attr2);
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        $found = $this->productRepository->findByExternalCode('test-attr-123');
        $this->assertNotNull($found);
        $this->assertCount(2, $found->getAttributes());
        
        $attrs = [];
        foreach ($found->getAttributes() as $attr) {
            $attrs[$attr->getKey()] = $attr->getValue();
        }
        $this->assertEquals('Red', $attrs['Color']);
        $this->assertEquals('M', $attrs['Size']);
    }

    public function testImagesRelationship(): void
    {
        $product = new Product();
        $product->setExternalCode('test-img-123');
        $product->setName('Product with Images');
        $product->setPrice('75.00');
        
        $image1 = new ProductImage();
        $image1->setUrl('http://example.com/image1.jpg');
        $image1->setIsMain(true);
        $image1->setSortOrder(0);
        $product->addImage($image1);
        
        $image2 = new ProductImage();
        $image2->setUrl('http://example.com/image2.jpg');
        $image2->setIsMain(false);
        $image2->setSortOrder(1);
        $product->addImage($image2);
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        $found = $this->productRepository->findByExternalCode('test-img-123');
        $this->assertNotNull($found);
        $this->assertCount(2, $found->getImages());
        
        $mainImage = $found->getImages()->first();
        $this->assertTrue($mainImage->isMain());
    }
}