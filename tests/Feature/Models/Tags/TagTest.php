<?php

namespace Tests\Feature\Models\Tags;

use App\Models\Cmsrs\Page;
use App\Models\Cmsrs\Product;
use App\Models\Cmsrs\Tags\Tag;
use App\Models\Cmsrs\Tags\TagCategory;
use App\Models\Cmsrs\Tags\TagTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Cmsrs\Tags\TagCategoryTranslation;
// use Illuminate\Foundation\Testing\WithFaker;
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

    public function test_delete_tag_deletes_translations_and_taggables(): void
    {
        $page = Page::create();

        $tag = Tag::create([
            'tag_category_id' => TagCategory::create()->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'book',
        ]);

        $page->tags()->attach($tag->id, [
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
        ]);

        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
        ]);

        $tag->delete();

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('tag_translations', [
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
        ]);        
    }

    public function test_delete_category_deletes_related_tags_translations_and_taggables(): void
    {
        $category1 = TagCategory::create();
        $category2 = TagCategory::create();

        $TagCategoryTranslation1 = TagCategoryTranslation::create([
            'tag_category_id' => $category1->id,
            'lang' => 'en',
            'value' => 'Product Type']);

        //masz jakis lepszy pomysl na nazwe?
        $TagCategoryTranslation2 = TagCategoryTranslation::create([
            'tag_category_id' => $category2->id,
            'lang' => 'en',
            'value' => 'Product Type - Style']); //masz jakis lepszy pomysl na nazwe?


        $tag1 = Tag::create([
            'tag_category_id' => $category1->id,
        ]);

        $tag2 = Tag::create([
            'tag_category_id' => $category1->id,
        ]);

        $tag3 = Tag::create([
            'tag_category_id' => $category2->id,
        ]);

        $page1 = Page::create();
        $page2 = Page::create();

        TagTranslation::create([
            'tag_id' => $tag1->id,
            'lang' => 'en',
            'value' => 'trousers',
        ]);

        TagTranslation::create([
            'tag_id' => $tag2->id,
            'lang' => 'en',
            'value' => 'shoes',
        ]);

        TagTranslation::create([
            'tag_id' => $tag3->id,
            'lang' => 'en',
            'value' => 'book',
        ]);

        $page1->tags()->attach($tag1->id, [
            'lang' => 'en',
        ]);

        $page1->tags()->attach($tag2->id, [
            'lang' => 'en',
        ]);

        $page2->tags()->attach($tag3->id, [
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('tag_categories_translations', [
            'id' => $TagCategoryTranslation1->id,
        ]);

        $this->assertDatabaseHas('tag_categories_translations', [
            'id' => $TagCategoryTranslation2->id,
        ]);


        // category1, tag1, tag2 i ich zależności istnieją
        $this->assertDatabaseHas('tag_categories', [
            'id' => $category1->id,
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag2->id,
        ]);

        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag2->id,
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag2->id,
        ]);

        // Usuwamy kategorię
        $category1->delete();

        $this->assertDatabaseMissing('tag_categories_translations', [
            'id' => $TagCategoryTranslation1->id,
        ]);

        $this->assertDatabaseHas('tag_categories_translations', [
            'id' => $TagCategoryTranslation2->id,
        ]);


        // Kategoria została usunięta
        $this->assertDatabaseMissing('tag_categories', [
            'id' => $category1->id,
        ]);

        // Jej tagi zostały usunięte
        $this->assertDatabaseMissing('tags', [
            'id' => $tag1->id,
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag2->id,
        ]);

        // Tłumaczenia tych tagów zostały usunięte
        $this->assertDatabaseMissing('tag_translations', [
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseMissing('tag_translations', [
            'tag_id' => $tag2->id,
        ]);

        // Powiązania tych tagów zostały usunięte
        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag2->id,
        ]);

        // Druga kategoria nadal istnieje
        $this->assertDatabaseHas('tag_categories', [
            'id' => $category2->id,
        ]);

        // Tag z drugiej kategorii nadal istnieje
        $this->assertDatabaseHas('tags', [
            'id' => $tag3->id,
        ]);

        // Jego tłumaczenie nadal istnieje
        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag3->id,
        ]);

        // Jego powiązanie z Page nadal istnieje
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag3->id,
            'taggable_id' => $page2->id,
            'taggable_type' => Page::class,
            'lang' => 'en',
        ]);

        // Page nie zostały usunięte
        $this->assertDatabaseHas('pages', [
            'id' => $page1->id,
        ]);

        $this->assertDatabaseHas('pages', [
            'id' => $page2->id,
        ]);
    }    


}
