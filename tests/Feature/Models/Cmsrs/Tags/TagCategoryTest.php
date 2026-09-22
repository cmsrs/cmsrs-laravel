<?php

namespace Tests\Feature\Models\Cmsrs\Tags;

use App\Models\Cmsrs\Tags\Tag;
use App\Models\Cmsrs\Tags\TagCategory;
use App\Models\Cmsrs\Tags\TagCategoryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

class TagCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_many_tags(): void
    {
        $category = TagCategory::create();
        $tag1 = Tag::create(['tag_category_id' => $category->id]);
        $tag2 = Tag::create(['tag_category_id' => $category->id]);

        $this->assertEquals(2, $category->tags->count());
        $this->assertEquals($category->tags[0]->id, $tag1->id);
        $this->assertEquals($category->tags[1]->id, $tag2->id);
    }

    public function test_has_many_translations(): void
    {
        $category = TagCategory::create();
        $TagCategoryTranslation1 = TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product Type']);
        $TagCategoryTranslation2 = TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ Produktu',
        ]);

        $this->assertEquals(2, $category->translations->count());
        $this->assertEquals($category->translations[0]->id, $TagCategoryTranslation1->id);
        $this->assertEquals($category->translations[1]->id, $TagCategoryTranslation2->id);

        $this->assertEquals($category->id, $TagCategoryTranslation1->tag_category_id);
        $this->assertEquals($category->id, $TagCategoryTranslation2->tag_category_id);
    }
}
