<?php

namespace Tests\Feature\Models;

use App\Models\Cmsrs\Tags\TagCategory;
use App\Models\Cmsrs\Tags\Tag;
use App\Models\Cmsrs\Product;
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


        $product->tags()->attach($tag1->id, ['lang' => 'en']);
        $product->tags()->attach($tag2->id, ['lang' => 'en']);

        $dbTaggableFields1 = $product->tags()->first()->pivot->toArray();
        $dbTaggableFields2 = $product->tags()->get()[1]->pivot->toArray();

        $this->assertEquals($dbTaggableFields1['taggable_type'], Product::class);
        $this->assertEquals($dbTaggableFields1['taggable_id'], $product->id);
        $this->assertEquals($dbTaggableFields1['tag_id'], $tag1->id);
        $this->assertEquals($dbTaggableFields1['lang'], 'en');

        $this->assertEquals($dbTaggableFields2['taggable_type'], Product::class);
        $this->assertEquals($dbTaggableFields2['taggable_id'], $product->id);
        $this->assertEquals($dbTaggableFields2['tag_id'], $tag2->id);
        $this->assertEquals($dbTaggableFields2['lang'], 'en');

        $this->assertEquals($product->tags()->first()->id, $tag1->id);
        $this->assertEquals($product->tags()->get()[1]->id, $tag2->id);


        $this->assertEquals(2, $product->tags()->count());
        $this->assertEquals(1, $tag1->products()->count());
        $this->assertEquals(1, $tag2->products()->count());

        $tag1->products()->attach($product->id, ['lang' => 'pl']);
        $this->assertEquals(2, $tag1->products()->count());

        $taggableRecords = \DB::table('taggables')->get();
        $this->assertEquals(3, $taggableRecords->count());

        $taggableData = $taggableRecords->toArray();
        foreach ($taggableData as $record) {
            $this->assertEquals(Product::class, $record->taggable_type);
            $this->assertEquals($product->id, $record->taggable_id);
        }
        
        //to zglasza wyjatek i to jest wlasiwe zachowanie!!!!!!!! - bo jest uniq - ale z drugiej strony to robimy
        $this->expectException(UniqueConstraintViolationException::class);
        $tag1->products()->attach($product->id, ['lang' => 'en']);
    }

    public function test_product_tags_unique_exception(): void
    {
        $product = Product::create(); 
        $tag1 = Tag::create(['tag_category_id' => TagCategory::create()->id]);        


        $product->tags()->attach($tag1->id, ['lang' => 'en']);
        $this->expectException(UniqueConstraintViolationException::class);
        $product->tags()->attach($tag1->id, ['lang' => 'en']);        
    }    
    
}
