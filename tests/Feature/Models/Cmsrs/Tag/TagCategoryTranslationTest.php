<?php

namespace Tests\Feature\Models\Cmsrs\Tag;

use App\Models\Cmsrs\Tag\TagCategory;
use App\Models\Cmsrs\Tag\TagCategoryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCategoryTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_tag_category(): void
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

        $this->assertEquals($category->id, $TagCategoryTranslation1->category->id);
        $this->assertEquals($category->id, $TagCategoryTranslation2->category->id);

    }
}
