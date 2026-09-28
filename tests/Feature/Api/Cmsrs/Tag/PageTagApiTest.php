<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Tag;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTagApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_page_tags(): void
    {
        $page = Page::factory()->create();

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        TagTranslation::create([
            'tag_id' => $tag1->id,
            'lang' => 'en',
            'value' => 'News',
        ]);

        TagTranslation::create([
            'tag_id' => $tag2->id,
            'lang' => 'en',
            'value' => 'Laravel',
        ]);

        TagTranslation::create([
            'tag_id' => $tag3->id,
            'lang' => 'pl',
            'value' => 'Aktualności',
        ]);

        $page->tags()->attach($tag1->id, [
            'lang' => 'en',
        ]);

        $page->tags()->attach($tag2->id, [
            'lang' => 'en',
        ]);

        $page->tags()->attach($tag3->id, [
            'lang' => 'pl',
        ]);

        $response = $this->getJson(
            "/api/pages/{$page->id}/tags"
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath(
                'data.en.0.id',
                $tag1->id
            )
            ->assertJsonPath(
                'data.en.1.id',
                $tag2->id
            )
            ->assertJsonPath(
                'data.pl.0.id',
                $tag3->id
            );
    }

    public function test_update_page_tags(): void
    {
        $page = Page::factory()->create();

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $page->tags()->attach($tag1->id, [
            'lang' => 'en',
        ]);

        $response = $this->putJson(
            "/api/pages/{$page->id}/tags",
            [
                'tags' => [
                    'en' => [
                        $tag2->id,
                        $tag3->id,
                    ],
                    'pl' => [
                        $tag1->id,
                    ],
                ],
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Page::class,
            'taggable_id' => $page->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag2->id,
            'taggable_type' => Page::class,
            'taggable_id' => $page->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag3->id,
            'taggable_type' => Page::class,
            'taggable_id' => $page->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Page::class,
            'taggable_id' => $page->id,
            'lang' => 'pl',
        ]);
    }

    public function test_update_page_tags_requires_tags(): void
    {
        $page = Page::factory()->create();

        $response = $this->putJson(
            "/api/pages/{$page->id}/tags",
            []
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_update_page_tags_requires_array_for_language(): void
    {
        $page = Page::factory()->create();

        $response = $this->putJson(
            "/api/pages/{$page->id}/tags",
            [
                'tags' => [
                    'en' => 'wrong',
                ],
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_update_page_tags_fails_for_non_existing_tag(): void
    {
        $page = Page::factory()->create();

        $response = $this->putJson(
            "/api/pages/{$page->id}/tags",
            [
                'tags' => [
                    'en' => [
                        999999,
                    ],
                ],
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_get_pages_by_tag(): void
    {
        $tag = Tag::factory()->create();

        $page1 = Page::factory()->create();
        $page2 = Page::factory()->create();
        $page3 = Page::factory()->create();

        $page1->tags()->attach($tag->id, [
            'lang' => 'en',
        ]);

        $page2->tags()->attach($tag->id, [
            'lang' => 'en',
        ]);

        $page3->tags()->attach($tag->id, [
            'lang' => 'pl',
        ]);

        $response = $this->getJson(
            "/api/pages/tag/{$tag->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_get_pages_by_tag_and_language(): void
    {
        $tag = Tag::factory()->create();

        $page1 = Page::factory()->create();
        $page2 = Page::factory()->create();
        $page3 = Page::factory()->create();

        $page1->tags()->attach($tag->id, [
            'lang' => 'en',
        ]);

        $page2->tags()->attach($tag->id, [
            'lang' => 'en',
        ]);

        $page3->tags()->attach($tag->id, [
            'lang' => 'pl',
        ]);

        $response = $this->getJson(
            "/api/pages/tag/{$tag->id}?lang=en"
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_get_page_tags_returns_empty_data_when_page_has_no_tags(): void
    {
        $page = Page::factory()->create();

        $response = $this->getJson(
            "/api/pages/{$page->id}/tags"
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }
}
