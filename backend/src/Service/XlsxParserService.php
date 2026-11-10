<?php

declare(strict_types=1);

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XlsxParserService
{
    private const HEADER_MAPPING = [
        'Группы' => 'category_group',
        'UUID' => 'external_code',
        'Тип' => 'type',
        'Код' => 'sku',
        'Наименование' => 'name',
        'Внешний код' => 'external_code_alt',
        'Артикул' => 'article',
        'Единица измерения' => 'unit',
        'Цена: Цена продажи' => 'price',
        'Валюта (Цена продажи)' => 'currency',
        'Описание' => 'description',
        'Закупочная цена' => 'purchase_price',
        'Валюта (Закупочная цена)' => 'purchase_currency',
        'Штрихкод EAN13' => 'barcode_ean13',
        'Штрихкод EAN8' => 'barcode_ean8',
        'Штрихкод Code128' => 'barcode_code128',
        'Штрихкод UPC' => 'barcode_upc',
        'Штрихкод GTIN' => 'barcode_gtin',
        'Признак предмета расчета' => 'calculation_subject',
        'Запретить скидки при продаже в розницу' => 'no_discount',
        'Минимальная цена' => 'min_price',
        'Валюта (Минимальная цена)' => 'min_price_currency',
        'Страна' => 'country',
        'НДС' => 'vat',
        'Поставщик' => 'supplier',
        'Архивный' => 'is_archived',
        'Вес' => 'weight',
        'Весовой товар' => 'is_weight_product',
        'Маркированная продукция' => 'is_marked',
        'Объем' => 'volume',
        'Содержит акцизную марку' => 'has_excise_mark',
        'Доп. поле: Размер' => 'attr_Размер',
        'Доп. поле: Цвет' => 'attr_Цвет',
        'Доп. поле: Бренд' => 'attr_Бренд',
        'Доп. поле: Состав' => 'attr_Состав',
        'Доп. поле: Кол-во в упаковке' => 'attr_Кол-во в упаковке',
        'Доп. поле: Ссылка на упаковку' => 'attr_Ссылка на упаковку',
        'Доп. поле: Ссылки на фото' => 'images',
        'Доп. поле: seo title' => 'seo_title',
        'Доп. поле: seo h1' => 'seo_h1',
        'Доп. поле: seo description' => 'seo_description',
        'Доп. поле: Вес товара(г)' => 'attr_Вес товара(г)',
        'Доп. поле: Ширина(мм)' => 'attr_Ширина(мм)',
        'Доп. поле: Высота(мм)' => 'attr_Высота(мм)',
        'Доп. поле: Длина(мм)' => 'attr_Длина(мм)',
        'Доп. поле: Вес упаковки(г)' => 'attr_Вес упаковки(г)',
        'Доп. поле: Ширина упаковки(мм)' => 'attr_Ширина упаковки(мм)',
        'Доп. поле: Высота упаковки(мм)' => 'attr_Высота упаковки(мм)',
        'Доп. поле: Длина упаковки(мм)' => 'attr_Длина упаковки(мм)',
        'Доп. поле: Категория товара' => 'category',
    ];

    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        $headers = $this->getHeaders($worksheet);
        $rows = [];
        
        foreach ($worksheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $rowData = [];
            $colIndex = 0;
            
            foreach ($cellIterator as $cell) {
                $header = $headers[$colIndex] ?? "col_$colIndex";
                $value = $cell->getValue();
                
                // Convert value to string if it's not null
                if ($value !== null && $value !== '') {
                    $rowData[$header] = is_scalar($value) ? (string)$value : '';
                }
                
                $colIndex++;
            }
            
            // Only add rows that have at least external_code or name
            if (!empty($rowData['UUID']) || !empty($rowData['Наименование'])) {
                $rows[] = $this->mapRow($rowData);
            }
        }
        
        return $rows;
    }

    private function getHeaders(Worksheet $worksheet): array
    {
        $headers = [];
        $firstRow = $worksheet->getRowIterator(1)->current();
        
        if ($firstRow) {
            $cellIterator = $firstRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            foreach ($cellIterator as $cell) {
                $headers[] = $cell->getValue();
            }
        }
        
        return $headers;
    }

    private function mapRow(array $rowData): array
    {
        $mapped = [];
        
        foreach ($rowData as $header => $value) {
            $mappedKey = self::HEADER_MAPPING[$header] ?? $header;
            $mapped[$mappedKey] = $value;
        }
        
        // Process price fields - convert from "900,00" format to "900.00"
        if (isset($mapped['price'])) {
            $mapped['price'] = $this->parsePrice($mapped['price']);
        }
        if (isset($mapped['purchase_price'])) {
            $mapped['purchase_price'] = $this->parsePrice($mapped['purchase_price']);
        }
        if (isset($mapped['min_price'])) {
            $mapped['min_price'] = $this->parsePrice($mapped['min_price']);
        }
        
        // Calculate discount if we have both prices
        if (isset($mapped['price']) && isset($mapped['purchase_price'])) {
            $price = (float)$mapped['price'];
            $purchasePrice = (float)$mapped['purchase_price'];
            if ($price > 0) {
                $discount = (($price - $purchasePrice) / $price) * 100;
                $mapped['discount'] = number_format($discount, 2, '.', '');
            }
        }
        
        // Process boolean fields
        $booleanFields = ['no_discount', 'is_archived', 'is_weight_product', 'is_marked', 'has_excise_mark'];
        foreach ($booleanFields as $field) {
            if (isset($mapped[$field])) {
                $mapped[$field] = $this->parseBoolean($mapped[$field]);
            }
        }
        
        // Process numeric fields
        $numericFields = ['weight', 'volume', 'package_quantity', 'product_weight', 'width', 'height', 'length', 'package_weight', 'package_width', 'package_height', 'package_length'];
        foreach ($numericFields as $field) {
            if (isset($mapped[$field])) {
                $mapped[$field] = $this->parseNumeric($mapped[$field]);
            }
        }
        
        // Process images - split by comma
        if (isset($mapped['images'])) {
            $imageUrls = array_map('trim', explode(',', $mapped['images']));
            $mapped['images'] = array_map(function ($url, $index) {
                return [
                    'url' => $url,
                    'sort_order' => $index,
                    'is_main' => $index === 0,
                ];
            }, $imageUrls, array_keys($imageUrls));
        } else {
            $mapped['images'] = [];
        }
        
        // Collect attributes
        $attributes = [];
        foreach ($mapped as $key => $value) {
            if (str_starts_with($key, 'attr_')) {
                $attrKey = substr($key, 5);
                $attributes[$attrKey] = $value;
            }
        }
        $mapped['attributes'] = $attributes;
        
        // Map main fields
        $result = [
            'external_code' => $mapped['external_code'] ?? $mapped['external_code_alt'] ?? '',
            'name' => $mapped['name'] ?? '',
            'description' => $mapped['description'] ?? null,
            'price' => $mapped['price'] ?? '0.00',
            'discount' => $mapped['discount'] ?? null,
            'currency' => $mapped['currency'] ?? 'RUB',
            'sku' => $mapped['sku'] ?? null,
            'unit' => $mapped['unit'] ?? null,
            'country' => $mapped['country'] ?? null,
            'vat' => $mapped['vat'] ?? null,
            'brand' => $mapped['attr_Бренд'] ?? $mapped['supplier'] ?? null,
            'barcode' => $mapped['barcode_ean13'] ?? $mapped['barcode_gtin'] ?? null,
            'is_archived' => $mapped['is_archived'] ?? false,
            'weight' => $mapped['weight'] ?? null,
            'is_weight_product' => $mapped['is_weight_product'] ?? false,
            'is_marked' => $mapped['is_marked'] ?? false,
            'volume' => $mapped['volume'] ?? null,
            'has_excise_mark' => $mapped['has_excise_mark'] ?? false,
            'category' => $mapped['category'] ?? $mapped['category_group'] ?? null,
            'seo_title' => $mapped['seo_title'] ?? null,
            'seo_h1' => $mapped['seo_h1'] ?? null,
            'seo_description' => $mapped['seo_description'] ?? null,
            'package_quantity' => $mapped['attr_Кол-во в упаковке'] ?? null,
            'package_url' => $mapped['attr_Ссылка на упаковку'] ?? null,
            'packaging_url' => $mapped['attr_Ссылки на фото'] ?? null,
            'product_weight' => $mapped['attr_Вес товара(г)'] ?? null,
            'width' => $mapped['attr_Ширина(мм)'] ?? null,
            'height' => $mapped['attr_Высота(мм)'] ?? null,
            'length' => $mapped['attr_Длина(мм)'] ?? null,
            'package_weight' => $mapped['attr_Вес упаковки(г)'] ?? null,
            'package_width' => $mapped['attr_Ширина упаковки(мм)'] ?? null,
            'package_height' => $mapped['attr_Высота упаковки(мм)'] ?? null,
            'package_length' => $mapped['attr_Длина упаковки(мм)'] ?? null,
            'attributes' => $mapped['attributes'],
            'images' => $mapped['images'],
        ];
        
        return $result;
    }

    private function parsePrice(string $price): string
    {
        // Replace comma with dot, remove spaces
        $cleaned = str_replace([' ', ','], ['', '.'], $price);
        return $cleaned;
    }

    private function parseBoolean(string $value): bool
    {
        $value = mb_strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'да', 'on'], true);
    }

    private function parseNumeric(string $value): ?int
    {
        $cleaned = str_replace([' ', ','], ['', '.'], $value);
        $numeric = filter_var($cleaned, FILTER_VALIDATE_FLOAT);
        return $numeric !== false ? (int)$numeric : null;
    }
}