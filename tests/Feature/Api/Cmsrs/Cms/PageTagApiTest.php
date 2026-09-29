<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Cms;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use App\Models\Cmsrs\Tag\TagTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Services\Cmsrs\Base;

class PageTagApiTest extends Base
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        putenv('LANGS="en,pl"');
        putenv('API_SECRET=""');
        putenv('CURRENCY="USD"');
        putenv('CACHE_ENABLE=false');
        putenv('CACHE_ENABLE_FILE="app/cache_enable_test.txt"');
        putenv('DEMO_STATUS=false');
        putenv('IS_SHOP=true');
        putenv('IS_LOGIN=true');
        putenv('IS_REGISTER=true');
        putenv('IS_HEADLESS=false');
        putenv('IS_SSR=true');

        parent::setUp();

        $this->createUser();
    }

    private function createPage(): Page
    {
        return Page::create([
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'position' => 1,
            'type' => 'cms',
        ]);

    }

    private function createTag(
        string $en,
        string $pl
    ): Tag {
        $category = TagCategory::create();

        $tag = Tag::create([
            'tag_category_id' => $category->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => $en,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => $pl,
        ]);

        return $tag;
    }

    public function test_it_will_add_tags_to_page_put_docs(): void
    {
        $page = $this->createPage();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $data = [
            'tags' => [
                'en' => [
                    $tag1->id,
                    $tag2->id,
                ],
                'pl' => [
                    $tag1->id,
                ],
            ],
        ];

        $response = $this->put(
            'api/pages/'.$page->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 3);

        $this->assertDatabaseHas('taggables', [
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
            'tag_id' => $tag1->id,
            'taggable_type' => Page::class,
            'taggable_id' => $page->id,
            'lang' => 'pl',
        ]);
    }

    public function test_it_will_return_page_tags_get_docs(): void
    {
        $page = $this->createPage();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $page->tags()->attach(
            $tag1->id,
            ['lang' => 'en']
        );

        $page->tags()->attach(
            $tag1->id,
            ['lang' => 'pl']
        );

        $page->tags()->attach(
            $tag2->id,
            ['lang' => 'en']
        );

        $response = $this->get(
            'api/pages/'.$page->id.'/tags'.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertCount(2, $res->data->en);
        $this->assertCount(1, $res->data->pl);

        $this->assertEquals(
            $tag1->id,
            $res->data->en[0]->id
        );

        $this->assertEquals(
            'Shoes',
            $res->data->en[0]->name
        );

        $this->assertEquals(
            'en',
            $res->data->en[0]->lang
        );

        $this->assertEquals(
            $tag1->id,
            $res->data->pl[0]->id
        );

        $this->assertEquals(
            'Buty',
            $res->data->pl[0]->name
        );
    }

    public function test_it_will_return_pages_by_tag_get_docs(): void
    {
        $page1 = $this->createPage();
        $page2 = $this->createPage();

        $tag = $this->createTag(
            'Shoes',
            'Buty'
        );

        $page1->tags()->attach(
            $tag->id,
            ['lang' => 'en']
        );

        $page2->tags()->attach(
            $tag->id,
            ['lang' => 'pl']
        );

        $response = $this->get(
            'api/pages/tag/'.$tag->id.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertCount(2, $res->data);

        $this->assertEquals(
            $page1->id,
            $res->data[0]->id
        );

        $this->assertEquals(
            'en',
            $res->data[0]->lang
        );

        $this->assertEquals(
            $page2->id,
            $res->data[1]->id
        );

        $this->assertEquals(
            'pl',
            $res->data[1]->lang
        );
    }

    public function test_it_will_replace_page_tags(): void
    {
        $page = $this->createPage();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $tag3 = $this->createTag(
            'Running',
            'Bieganie'
        );

        $page->tags()->attach(
            $tag1->id,
            ['lang' => 'en']
        );

        $page->tags()->attach(
            $tag2->id,
            ['lang' => 'en']
        );

        $this->assertDatabaseCount('taggables', 2);

        $data = [
            'tags' => [
                'en' => [
                    $tag3->id,
                ],
            ],
        ];

        $response = $this->put(
            'api/pages/'.$page->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 1);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag1->id,
            'taggable_id' => $page->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag2->id,
            'taggable_id' => $page->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag3->id,
            'taggable_id' => $page->id,
            'lang' => 'en',
        ]);
    }

    public function test_it_will_return_empty_tags_for_page(): void
    {
        $page = $this->createPage();

        $response = $this->get(
            'api/pages/'.$page->id.'/tags'.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals([], $res->data);
    }

    public function test_it_will_not_update_page_tags_with_fake_tag(): void
    {
        $page = $this->createPage();

        $data = [
            'tags' => [
                'en' => [
                    99999,
                ],
            ],
        ];

        $response = $this->put(
            'api/pages/'.$page->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);

        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_not_update_page_tags_with_wrong_data(): void
    {
        $page = $this->createPage();

        $data = [
            'wrong' => [
                'en' => [
                    1,
                ],
            ],
        ];

        $response = $this->put(
            'api/pages/'.$page->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);

        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_return_404_for_fake_page(): void
    {
        $response = $this->get(
            'api/pages/99999/tags'.
            '?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_when_updating_fake_page(): void
    {
        $tag = $this->createTag(
            'Shoes',
            'Buty'
        );

        $data = [
            'tags' => [
                'en' => [
                    $tag->id,
                ],
            ],
        ];

        $response = $this->put(
            'api/pages/99999/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_for_fake_tag(): void
    {
        $response = $this->get(
            'api/pages/tag/99999'.
            '?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_add_tags_to_page_for_unknown_lang(): void
    {
        $page = $this->createPage();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $data = [
            'tags' => [
                'en' => [
                    $tag1->id,
                    $tag2->id,
                ],
                'de' => [
                    $tag1->id,
                ],
            ],
        ];

        $response = $this->put(
            'api/pages/'.$page->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 2);

        $this->assertDatabaseHas('taggables', [
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

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Page::class,
            'taggable_id' => $page->id,
            'lang' => 'de',
        ]);
    }
}
