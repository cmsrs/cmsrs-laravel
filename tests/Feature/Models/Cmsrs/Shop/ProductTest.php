<?php

namespace Tests\Feature\Models\Cmsrs\Shop;

use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_many_tags_by_product_morph(): void
    {
        $product = Product::create();
        $tag1 = Tag::create(['tag_category_id' => TagCategory::create()->id]);
        $tag2 = Tag::create(['tag_category_id' => TagCategory::create()->id]);

        $product->tags()->attach($tag1->id);
        $product->tags()->attach($tag2->id);

        $dbTaggableFields1 = $product->tags()->first()->pivot->toArray();
        $dbTaggableFields2 = $product->tags()->get()[1]->pivot->toArray();

        $this->assertEquals($dbTaggableFields1['taggable_type'], Product::class);
        $this->assertEquals($dbTaggableFields1['taggable_id'], $product->id);
        $this->assertEquals($dbTaggableFields1['tag_id'], $tag1->id);

        $this->assertEquals($dbTaggableFields2['taggable_type'], Product::class);
        $this->assertEquals($dbTaggableFields2['taggable_id'], $product->id);
        $this->assertEquals($dbTaggableFields2['tag_id'], $tag2->id);

        $this->assertEquals($product->tags()->first()->id, $tag1->id);
        $this->assertEquals($product->tags()->get()[1]->id, $tag2->id);

        $this->assertEquals(2, $product->tags()->count());
        $this->assertEquals(1, $tag1->products()->count());
        $this->assertEquals(1, $tag2->products()->count());

        $taggableRecords = \DB::table('taggables')->get();
        $this->assertEquals(2, $taggableRecords->count());

        $taggableData = $taggableRecords->toArray();
        foreach ($taggableData as $record) {
            $this->assertEquals(Product::class, $record->taggable_type);
            $this->assertEquals($product->id, $record->taggable_id);
        }

        $this->expectException(UniqueConstraintViolationException::class);
        $tag1->products()->attach($product->id);
    }

    public function test_product_tags_unique_exception(): void
    {
        $product = Product::create();
        $tag1 = Tag::create(['tag_category_id' => TagCategory::create()->id]);

        $product->tags()->attach($tag1->id);
        $this->expectException(UniqueConstraintViolationException::class);
        $product->tags()->attach($tag1->id);
    }
}
