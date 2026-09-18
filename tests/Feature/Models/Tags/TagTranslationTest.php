<?php

namespace Tests\Feature\Models\Tags;

use App\Models\Cmsrs\Tags\Tag;
use App\Models\Cmsrs\Tags\TagCategory;
use App\Models\Cmsrs\Tags\TagTranslation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_tag(): void
    {
        $tag = Tag::create(['tag_category_id' => TagCategory::create()->id]);
        $tagTranslation1 = TagTranslation::create([
            'tag_id' => $tag->id, 
            'lang' => 'en', 
            'value' => 'book']);
        $tagTranslation2 = TagTranslation::create([
            'tag_id' => $tag->id, 
            'lang' => 'pl', 
            'value' => 'ksiazka'
        ]);

        $this->assertEquals($tag->id, $tagTranslation1->tag->id);
        $this->assertEquals($tag->id, $tagTranslation2->tag->id);

    }
}
