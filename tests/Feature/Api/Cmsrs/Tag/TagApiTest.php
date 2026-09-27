<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Tag;

use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use App\Models\Cmsrs\Tag\TagTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Services\Cmsrs\Base;

class TagApiTest extends Base
{
    use RefreshDatabase;

    private array $testData;

    private TagCategory $category;

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

        $this->category = TagCategory::create();

        $this->testData = [
            'tag_category_id' => $this->category->id,
            'name' => [
                'en' => 'Shoes',
                'pl' => 'Buty',
            ],
        ];
    }

    public function test_it_will_add_tag(): void
    {
        $response = $this->post(
            'api/tags?token='.$this->token,
            $this->testData
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $tag = Tag::first();

        $this->assertNotNull($tag);

        $this->assertEquals(
            $this->category->id,
            $tag->tag_category_id
        );

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'tag_category_id' => $this->category->id,
        ]);

        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'Shoes',
        ]);

        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => 'Buty',
        ]);
    }

    public function test_it_will_return_all_tags(): void
    {
        $tag1 = Tag::create([
            'tag_category_id' => $this->category->id,
        ]);

        $tag2 = Tag::create([
            'tag_category_id' => $this->category->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag1->id,
            'lang' => 'en',
            'value' => 'Shoes',
        ]);

        TagTranslation::create([
            'tag_id' => $tag1->id,
            'lang' => 'pl',
            'value' => 'Buty',
        ]);

        TagTranslation::create([
            'tag_id' => $tag2->id,
            'lang' => 'en',
            'value' => 'T-Shirts',
        ]);

        TagTranslation::create([
            'tag_id' => $tag2->id,
            'lang' => 'pl',
            'value' => 'Koszulki',
        ]);

        $response = $this->get(
            'api/tags?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);
        $this->assertCount(2, $res->data);

        $this->assertEquals(
            'Shoes',
            $res->data[0]->name->en
        );

        $this->assertEquals(
            'Buty',
            $res->data[0]->name->pl
        );

        $this->assertEquals(
            $this->category->id,
            $res->data[0]->tag_category_id
        );
    }

    public function test_it_will_return_one_tag(): void
    {
        $tag = Tag::create([
            'tag_category_id' => $this->category->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'Shoes',
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => 'Buty',
        ]);

        $response = $this->get(
            'api/tags/'.$tag->id.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals(
            $tag->id,
            $res->data->id
        );

        $this->assertEquals(
            $this->category->id,
            $res->data->tag_category_id
        );

        $this->assertEquals(
            'Shoes',
            $res->data->name->en
        );

        $this->assertEquals(
            'Buty',
            $res->data->name->pl
        );
    }

    public function test_it_will_update_tag(): void
    {
        $tag = Tag::create([
            'tag_category_id' => $this->category->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'Old shoes',
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => 'Stare buty',
        ]);

        $data = [
            'tag_category_id' => $this->category->id,
            'name' => [
                'en' => 'New shoes',
                'pl' => 'Nowe buty',
            ],
        ];

        $response = $this->put(
            'api/tags/'.$tag->id.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'New shoes',
        ]);

        $this->assertDatabaseHas('tag_translations', [
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => 'Nowe buty',
        ]);

        $this->assertDatabaseMissing('tag_translations', [
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'Old shoes',
        ]);
    }

    public function test_it_will_change_tag_category(): void
    {
        $newCategory = TagCategory::create();

        $tag = Tag::create([
            'tag_category_id' => $this->category->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'Shoes',
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => 'Buty',
        ]);

        $data = [
            'tag_category_id' => $newCategory->id,
            'name' => [
                'en' => 'Shoes',
                'pl' => 'Buty',
            ],
        ];

        $response = $this->put(
            'api/tags/'.$tag->id.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'tag_category_id' => $newCategory->id,
        ]);
    }

    public function test_it_will_delete_tag(): void
    {
        $tag = Tag::create([
            'tag_category_id' => $this->category->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'Shoes',
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => 'Buty',
        ]);

        $response = $this->delete(
            'api/tags/'.$tag->id.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('tag_translations', [
            'tag_id' => $tag->id,
        ]);
    }

    public function test_it_will_not_add_tag_with_empty_name(): void
    {
        $data = [
            'tag_category_id' => $this->category->id,
            'name' => [
                'en' => '',
                'pl' => 'Buty',
            ],
        ];

        $response = $this->post(
            'api/tags?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tags', 0);
    }

    public function test_it_will_not_add_tag_when_language_is_missing(): void
    {
        $data = [
            'tag_category_id' => $this->category->id,
            'name' => [
                'en' => 'Shoes',
            ],
        ];

        $response = $this->post(
            'api/tags?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tags', 0);
    }

    public function test_it_will_not_add_tag_without_category(): void
    {
        $data = [
            'tag_category_id' => 99999,
            'name' => [
                'en' => 'Shoes',
                'pl' => 'Buty',
            ],
        ];

        $response = $this->post(
            'api/tags?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tags', 0);
    }

    public function test_it_will_not_add_tag_with_duplicate_name(): void
    {
        $tag = Tag::create([
            'tag_category_id' => $this->category->id,
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'en',
            'value' => 'Shoes',
        ]);

        TagTranslation::create([
            'tag_id' => $tag->id,
            'lang' => 'pl',
            'value' => 'Buty',
        ]);

        $data = [
            'tag_category_id' => $this->category->id,
            'name' => [
                'en' => 'Shoes',
                'pl' => 'Other',
            ],
        ];

        $response = $this->post(
            'api/tags?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tags', 1);
    }

    public function test_it_will_return_404_for_fake_tag(): void
    {
        $response = $this->get(
            'api/tags/99999?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_when_deleting_fake_tag(): void
    {
        $response = $this->delete(
            'api/tags/99999?token='.$this->token
        );

        $response->assertStatus(404);
    }
}
