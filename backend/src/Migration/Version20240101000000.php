<?php

declare(strict_types=1);

namespace App\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial tables: products, product_attributes, product_images, users';
    }

    public function up(Schema $schema): void
    {
        // Create users table
        $users = $schema->createTable('users');
        $users->addColumn('id', 'uuid', ['notnull' => true]);
        $users->addColumn('email', 'string', ['length' => 255, 'notnull' => true]);
        $users->addColumn('password', 'string', ['length' => 255, 'notnull' => true]);
        $users->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
        $users->addColumn('is_active', 'boolean', ['notnull' => true, 'default' => true]);
        $users->addColumn('created_at', 'datetime_immutable', ['notnull' => true]);
        $users->addColumn('updated_at', 'datetime_immutable', ['notnull' => true]);
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['email']);

        // Create products table
        $products = $schema->createTable('products');
        $products->addColumn('id', 'uuid', ['notnull' => true]);
        $products->addColumn('external_code', 'string', ['length' => 255, 'notnull' => true]);
        $products->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
        $products->addColumn('description', 'text', ['notnull' => false]);
        $products->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => true]);
        $products->addColumn('discount', 'decimal', ['precision' => 5, 'scale' => 2, 'notnull' => false]);
        $products->addColumn('currency', 'string', ['length' => 50, 'notnull' => false, 'default' => 'RUB']);
        $products->addColumn('sku', 'string', ['length' => 255, 'notnull' => false]);
        $products->addColumn('unit', 'string', ['length' => 50, 'notnull' => false]);
        $products->addColumn('country', 'string', ['length' => 50, 'notnull' => false]);
        $products->addColumn('vat', 'string', ['length' => 50, 'notnull' => false]);
        $products->addColumn('brand', 'string', ['length' => 255, 'notnull' => false]);
        $products->addColumn('barcode', 'string', ['length' => 255, 'notnull' => false]);
        $products->addColumn('is_archived', 'boolean', ['notnull' => true, 'default' => false]);
        $products->addColumn('weight', 'decimal', ['precision' => 10, 'scale' => 3, 'notnull' => false]);
        $products->addColumn('is_weight_product', 'boolean', ['notnull' => true, 'default' => false]);
        $products->addColumn('is_marked', 'boolean', ['notnull' => true, 'default' => false]);
        $products->addColumn('volume', 'decimal', ['precision' => 10, 'scale' => 3, 'notnull' => false]);
        $products->addColumn('has_excise_mark', 'boolean', ['notnull' => true, 'default' => false]);
        $products->addColumn('category', 'string', ['length' => 255, 'notnull' => false]);
        $products->addColumn('seo_title', 'string', ['length' => 255, 'notnull' => false]);
        $products->addColumn('seo_h1', 'text', ['notnull' => false]);
        $products->addColumn('seo_description', 'text', ['notnull' => false]);
        $products->addColumn('package_quantity', 'integer', ['notnull' => false]);
        $products->addColumn('package_url', 'string', ['length' => 500, 'notnull' => false]);
        $products->addColumn('packaging_url', 'string', ['length' => 500, 'notnull' => false]);
        $products->addColumn('product_weight', 'integer', ['notnull' => false]);
        $products->addColumn('width', 'integer', ['notnull' => false]);
        $products->addColumn('height', 'integer', ['notnull' => false]);
        $products->addColumn('length', 'integer', ['notnull' => false]);
        $products->addColumn('package_weight', 'integer', ['notnull' => false]);
        $products->addColumn('package_width', 'integer', ['notnull' => false]);
        $products->addColumn('package_height', 'integer', ['notnull' => false]);
        $products->addColumn('package_length', 'integer', ['notnull' => false]);
        $products->addColumn('created_at', 'datetime_immutable', ['notnull' => true]);
        $products->addColumn('updated_at', 'datetime_immutable', ['notnull' => true]);
        $products->setPrimaryKey(['id']);
        $products->addUniqueIndex(['external_code']);
        $products->addIndex(['name']);

        // Create product_attributes table
        $attributes = $schema->createTable('product_attributes');
        $attributes->addColumn('id', 'uuid', ['notnull' => true]);
        $attributes->addColumn('product_id', 'uuid', ['notnull' => true]);
        $attributes->addColumn('attribute_key', 'string', ['length' => 255, 'notnull' => true]);
        $attributes->addColumn('attribute_value', 'text', ['notnull' => false]);
        $attributes->addColumn('created_at', 'datetime_immutable', ['notnull' => true]);
        $attributes->setPrimaryKey(['id']);
        $attributes->addIndex(['product_id', 'attribute_key']);
        $attributes->addForeignKeyConstraint('products', ['product_id'], ['id'], ['onDelete' => 'CASCADE']);

        // Create product_images table
        $images = $schema->createTable('product_images');
        $images->addColumn('id', 'uuid', ['notnull' => true]);
        $images->addColumn('product_id', 'uuid', ['notnull' => true]);
        $images->addColumn('url', 'string', ['length' => 1000, 'notnull' => true]);
        $images->addColumn('path', 'string', ['length' => 500, 'notnull' => false]);
        $images->addColumn('filename', 'string', ['length' => 255, 'notnull' => false]);
        $images->addColumn('size', 'integer', ['notnull' => false]);
        $images->addColumn('mime_type', 'string', ['length' => 100, 'notnull' => false]);
        $images->addColumn('is_main', 'boolean', ['notnull' => true, 'default' => false]);
        $images->addColumn('sort_order', 'integer', ['notnull' => true, 'default' => 0]);
        $images->addColumn('download_status', 'string', ['length' => 50, 'notnull' => false]);
        $images->addColumn('download_error', 'text', ['notnull' => false]);
        $images->addColumn('created_at', 'datetime_immutable', ['notnull' => true]);
        $images->addColumn('downloaded_at', 'datetime_immutable', ['notnull' => false]);
        $images->setPrimaryKey(['id']);
        $images->addIndex(['product_id']);
        $images->addForeignKeyConstraint('products', ['product_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('product_images');
        $schema->dropTable('product_attributes');
        $schema->dropTable('products');
        $schema->dropTable('users');
    }
}