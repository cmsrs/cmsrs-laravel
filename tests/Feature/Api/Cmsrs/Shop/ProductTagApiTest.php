<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Shop;

use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Tag\Tag;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Api\Cmsrs\Tag\TaggableApiCase;

class ProductTagApiTest extends TaggableApiCase
{
    protected function createTaggable(): Model
    {
        return Product::create([
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'position' => 1,
            'type' => 'cms',
        ]);
    }

    protected function taggableRoute(Model $model): string
    {
        return 'api/products/'.$model->id.'/tags';
    }

    protected function byTagRoute(Tag $tag): string
    {
        return 'api/products/tag/'.$tag->id;
    }

    protected function fakeTaggableRoute(): string
    {
        return 'api/products/99999/tags';
    }

    protected function byTagRouteForId(int $id): string
    {
        return 'api/products/tag/'.$id;
    }

    protected function taggableClass(): string
    {
        return Product::class;
    }
}
