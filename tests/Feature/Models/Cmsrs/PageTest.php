<?php

namespace Tests\Feature\Models\Cmsrs;

use App\Models\Cmsrs\Page;
use App\Models\Cmsrs\Tags\Tag;
use App\Models\Cmsrs\Tags\TagCategory;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_many_tags_by_page_morph(): void
    {
        $page = Page::create();
        $tag1 = Tag::create(['tag_category_id' => TagCategory::create()->id]);
        $tag2 = Tag::create(['tag_category_id' => TagCategory::create()->id]);

        $page->tags()->attach($tag1->id, ['lang' => 'en']);
        $page->tags()->attach($tag2->id, ['lang' => 'en']);

        $dbTaggableFields1 = $page->tags()->first()->pivot->toArray();
        $dbTaggableFields2 = $page->tags()->get()[1]->pivot->toArray();

        $this->assertEquals($dbTaggableFields1['taggable_type'], Page::class);
        $this->assertEquals($dbTaggableFields1['taggable_id'], $page->id);
        $this->assertEquals($dbTaggableFields1['tag_id'], $tag1->id);
        $this->assertEquals($dbTaggableFields1['lang'], 'en');

        $this->assertEquals($dbTaggableFields2['taggable_type'], Page::class);
        $this->assertEquals($dbTaggableFields2['taggable_id'], $page->id);
        $this->assertEquals($dbTaggableFields2['tag_id'], $tag2->id);
        $this->assertEquals($dbTaggableFields2['lang'], 'en');

        $this->assertEquals($page->tags()->first()->id, $tag1->id);
        $this->assertEquals($page->tags()->get()[1]->id, $tag2->id);

        $this->assertEquals(2, $page->tags()->count());
        $this->assertEquals(1, $tag1->pages()->count());
        $this->assertEquals(1, $tag2->pages()->count());

        $tag1->pages()->attach($page->id, ['lang' => 'pl']);
        $this->assertEquals(2, $tag1->pages()->count());

        $taggableRecords = \DB::table('taggables')->get();
        $this->assertEquals(3, $taggableRecords->count());

        $taggableData = $taggableRecords->toArray();
        foreach ($taggableData as $record) {
            $this->assertEquals(Page::class, $record->taggable_type);
            $this->assertEquals($page->id, $record->taggable_id);
        }

        // to zglasza wyjatek i to jest wlasiwe zachowanie!!!!!!!! - bo jest uniq - ale z drugiej strony to robimy
        $this->expectException(UniqueConstraintViolationException::class);
        $tag1->pages()->attach($page->id, ['lang' => 'en']);
    }

    public function test_page_tags_unique_exception(): void
    {
        $page = Page::create();
        $tag1 = Tag::create(['tag_category_id' => TagCategory::create()->id]);

        $page->tags()->attach($tag1->id, ['lang' => 'en']);
        $this->expectException(UniqueConstraintViolationException::class);
        $page->tags()->attach($tag1->id, ['lang' => 'en']);
    }
}
