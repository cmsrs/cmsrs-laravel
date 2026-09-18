<?php

namespace Tests\Feature\Models\Tags;

use App\Models\Cmsrs\Tags\TagCategory;
use App\Models\Cmsrs\Tags\Tag;
use App\Models\Cmsrs\Tags\TagTranslation;
use App\Models\Cmsrs\Page;
use App\Models\Cmsrs\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/*
(cetegory) Product Type
- (tag) book 
- trousers
- shoes

Style
- Casual
- Sport
- Elegant

Season
- Summer
- Winter

Color
- Black
- White
- Blue
*/

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_belongs_to_a_category(): void
    {
        $category = TagCategory::create();
        $categoryId = $category->id;
        $tag = Tag::create(['tag_category_id' => $categoryId]);
        $this->assertNotNull($tag->category);
        $this->assertEquals($categoryId, $tag->category->id);
    }

    public function test_tag_has_many_translations(): void
    {
        $tag = Tag::create(['tag_category_id' => TagCategory::create()->id]);
        TagTranslation::create(['tag_id' => $tag->id, 'lang' => 'en', 'value' => 'book']);
        TagTranslation::create(['tag_id' => $tag->id, 'lang' => 'pl', 'value' => 'ksiazka']);

        $this->assertEquals($tag->translations->count(), 2);
        $this->assertEquals($tag->translations[0]->value, 'book');
        $this->assertEquals($tag->translations[1]->value, 'ksiazka');
    }

    public function test_tag_has_many_pages_morph(): void
    {
        $tag = Tag::create(['tag_category_id' => TagCategory::create()->id]);
        $page1 = Page::create();
        $page2 = Page::create();

        $tag->pages()->attach($page1->id, ['lang' => 'en']);
        $tag->pages()->attach($page2->id, ['lang' => 'en']);

        $this->assertEquals(2, $tag->pages->count());
        $this->assertEquals($tag->pages[0]->id, $page1->id);
        $this->assertEquals($tag->pages[1]->id, $page2->id);
        

        $this->assertEquals('en', $tag->pages[0]->pivot->lang);
        $this->assertEquals('en', $tag->pages[1]->pivot->lang);

        $dbTaggableFields = $tag->pages[0]->pivot->toArray();
        $this->assertEquals($dbTaggableFields['taggable_type'], Page::class);
        $this->assertEquals($dbTaggableFields['taggable_id'], $page1->id);
        $this->assertEquals($dbTaggableFields['tag_id'], $tag->id);
        $this->assertEquals($dbTaggableFields['lang'], 'en');
    }

    public function test_tag_has_many_products_morph(): void
    {
        $tag = Tag::create(['tag_category_id' => TagCategory::create()->id]);
        $product1 = Product::create();
        $product2 = Product::create();

        $tag->products()->attach($product1->id, ['lang' => 'en']);
        $tag->products()->attach($product2->id, ['lang' => 'en']);

        $this->assertEquals(2, $tag->products->count());
        $this->assertEquals($tag->products[0]->id, $product1->id);
        $this->assertEquals($tag->products[1]->id, $product2->id);
        

        $this->assertEquals('en', $tag->products[0]->pivot->lang);
        $this->assertEquals('en', $tag->products[1]->pivot->lang);

        $dbTaggableFields = $tag->products[0]->pivot->toArray();
        $this->assertEquals($dbTaggableFields['taggable_type'], Product::class);
        $this->assertEquals($dbTaggableFields['taggable_id'], $product1->id);
        $this->assertEquals($dbTaggableFields['tag_id'], $tag->id);
        $this->assertEquals($dbTaggableFields['lang'], 'en');
    }

}