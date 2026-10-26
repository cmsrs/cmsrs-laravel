<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Shop;

use App\Models\Cmsrs\Shop\Product;
use Tests\Feature\Api\Cmsrs\Tag\TaggableCreateApiCase;

class ProductApiTest extends TaggableCreateApiCase
{
    protected function createRoute(): string
    {
        return 'api/products';
    }

    protected function createData(array $extra = []): array
    {
        return array_merge([
            'product_name' => ['en' => 'Test product', 'pl' => 'Produkt testowy'],
            'sku' => 'TEST-001',
            'price' => 123,
            'product_description' => ['en' => 'Description', 'pl' => 'Opis'],
        ], $extra);
    }

    protected function taggableClass(): string
    {
        return Product::class;
    }

    protected function entityType(): string
    {
        return 'product';
    }

    protected function tableName(): string
    {
        return 'products';
    }

    protected function idResponsePath(): string
    {
        return 'data.productId';
    }
}
