<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\ProductImage;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create test user
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $user->setName('Admin User');
        $manager->persist($user);

        // Create sample products
        $products = [
            [
                'external_code' => '11ee-0a80-00380035e949',
                'name' => 'Брюки модель "Легинсы" женские, Bordo, MINIMI, 48 (L), КИТАЙ',
                'description' => 'Женские эластичные легинсы с высокой линией талии. Превосходно адаптируются к любой фигуре.',
                'price' => '900.00',
                'purchase_price' => '600.00',
                'currency' => 'RUB',
                'sku' => 'BMi_A 2911-01_24',
                'unit' => 'шт',
                'country' => 'Китай',
                'vat' => 'без НДС',
                'brand' => 'MINIMI',
                'barcode' => '8000000961341',
                'category' => 'Женщинам_Брюки',
                'attributes' => [
                    'Размер' => '48 (L)',
                    'Цвет' => 'Bordo',
                    'Бренд' => 'MINIMI',
                    'Состав' => '92% Полиэстер, 8% Эластан',
                ],
                'images' => [
                    'http://catalog.collant.ru/pics/SNL-558148_p.jpg',
                    'http://catalog.collant.ru/pics/SNL-558148_m.jpg',
                ],
            ],
            [
                'external_code' => 'db9d355a-aa1b-11ee-0a80-00380035e9b7',
                'name' => 'Брюки модель "Легинсы" женские, Fumo, MINIMI, 54 (3XL), КИТАЙ',
                'description' => 'Женские эластичные легинсы с высокой линией талии. Превосходно адаптируются к любой фигуре.',
                'price' => '900.00',
                'purchase_price' => '600.00',
                'currency' => 'RUB',
                'sku' => 'BMi_A 2911-01_34',
                'unit' => 'шт',
                'country' => 'Китай',
                'vat' => 'без НДС',
                'brand' => 'MINIMI',
                'barcode' => '8000000961440',
                'category' => 'Женщинам_Брюки',
                'attributes' => [
                    'Размер' => '54 (3XL)',
                    'Цвет' => 'Fumo',
                    'Бренд' => 'MINIMI',
                    'Состав' => '92% Полиэстер, 8% Эластан',
                ],
                'images' => [
                    'http://catalog.collant.ru/pics/SNL-558148_p.jpg',
                    'http://catalog.collant.ru/pics/SNL-558148_b1.jpg',
                ],
            ],
            [
                'external_code' => 'e88c5849-aa1b-11ee-0a80-00380035fa31',
                'name' => 'Боди женское, Nero, MINIMI, 48 (L), КИТАЙ',
                'description' => 'Боди из эластичной сетки с регулируемыми бретелями и завышенной линией бедра.',
                'price' => '1899.00',
                'purchase_price' => '1266.00',
                'currency' => 'RUB',
                'sku' => 'LMi_T_NEON_1801-01SS_2',
                'unit' => 'шт',
                'country' => 'Китай',
                'vat' => 'без НДС',
                'brand' => 'MINIMI',
                'barcode' => '8000000942548',
                'category' => 'Женщинам_Белье',
                'attributes' => [
                    'Размер' => '48 (L)',
                    'Цвет' => 'Nero',
                    'Бренд' => 'MINIMI',
                    'Состав' => '83% Полиамид, 17% Эластан, 100% Ластовица Хлопок',
                ],
                'images' => [
                    'http://catalog.collant.ru/pics/SNL-561514_p.jpg',
                    'http://catalog.collant.ru/pics/SNL-561514_m.jpg',
                ],
            ],
        ];

        foreach ($products as $index => $productData) {
            $product = new Product();
            $product->setExternalCode($productData['external_code']);
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            
            // Calculate discount
            $price = (float)$productData['price'];
            $purchasePrice = (float)$productData['purchase_price'];
            if ($price > 0) {
                $discount = (($price - $purchasePrice) / $price) * 100;
                $product->setDiscount(number_format($discount, 2, '.', ''));
            }
            
            $product->setCurrency($productData['currency']);
            $product->setSku($productData['sku']);
            $product->setUnit($productData['unit']);
            $product->setCountry($productData['country']);
            $product->setVat($productData['vat']);
            $product->setBrand($productData['brand']);
            $product->setBarcode($productData['barcode']);
            $product->setCategory($productData['category']);

            // Add attributes
            foreach ($productData['attributes'] as $key => $value) {
                $attribute = new ProductAttribute();
                $attribute->setKey($key);
                $attribute->setValue($value);
                $product->addAttribute($attribute);
            }

            // Add images
            foreach ($productData['images'] as $imgIndex => $imageUrl) {
                $image = new ProductImage();
                $image->setUrl($imageUrl);
                $image->setFilename('placeholder_' . $imgIndex . '.jpg');
                $image->setIsMain($imgIndex === 0);
                $image->setSortOrder($imgIndex);
                $image->setDownloadStatus('pending');
                $product->addImage($image);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}