<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Tag;

use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use App\Models\Cmsrs\Tag\TagTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Services\Cmsrs\Base;

abstract class TaggableApiCase extends Base
{
    use RefreshDatabase;

    abstract protected function createTaggable(): Model;

    abstract protected function taggableRoute(Model $model): string;

    abstract protected function byTagRoute(Tag $tag): string;

    abstract protected function taggableClass(): string;

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

    public function test_it_will_add_tags_to_taggable_put_docs(): void
    {
        $taggable = $this->createTaggable();

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
                $tag1->id,
                $tag2->id,
            ],
        ];

        $response = $this->put(
            $this->taggableRoute($taggable).
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 2);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => $this->taggableClass(),
            'taggable_id' => $taggable->id,
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag2->id,
            'taggable_type' => $this->taggableClass(),
            'taggable_id' => $taggable->id,
        ]);

        $this->restDoc->add(
            'PUT',
            'api/pages_or_products/$id/tags?token=$token',
            $data,
            $res,
        );

    }

    public function test_it_will_return_taggable_tags_get_docs(): void
    {
        $taggable = $this->createTaggable();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $taggable->tags()->attach(
            $tag1->id
        );

        $taggable->tags()->attach(
            $tag2->id
        );

        $response = $this->get(
            $this->taggableRoute($taggable).
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertCount(2, $res->data->en);
        $this->assertCount(2, $res->data->pl);

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

        $this->assertEquals(
            'pl',
            $res->data->pl[0]->lang
        );

        $this->restDoc->add(
            'GET',
            'api/pages_or_products/$id/tags?token=$token',
            null,
            $res,
        );

    }

    public function test_it_will_return_taggables_by_tag_get_docs(): void
    {
        $taggable1 = $this->createTaggable();
        $taggable2 = $this->createTaggable();

        $tag = $this->createTag(
            'Shoes',
            'Buty'
        );

        $taggable1->tags()->attach(
            $tag->id
        );

        $taggable2->tags()->attach(
            $tag->id
        );

        $response = $this->get(
            $this->byTagRoute($tag).
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertCount(2, $res->data);

        $this->assertEquals(
            $taggable1->id,
            $res->data[0]->id
        );

        $this->assertFalse(
            property_exists($res->data[0], 'lang')
        );

        $this->assertEquals(
            $taggable2->id,
            $res->data[1]->id
        );

        $this->restDoc->add(
            'GET',
            'api/pages/tag/$tag_id?token=$token',
            null,
            $res,
        );

    }

    public function test_it_will_replace_taggable_tags(): void
    {
        $taggable = $this->createTaggable();

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

        $taggable->tags()->attach(
            $tag1->id
        );

        $taggable->tags()->attach(
            $tag2->id,
        );

        $this->assertDatabaseCount('taggables', 2);

        $data = [
            'tags' => [
                $tag3->id,
            ],
        ];

        $response = $this->put(
            $this->taggableRoute($taggable).
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 1);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag1->id,
            'taggable_id' => $taggable->id,
        ]);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag2->id,
            'taggable_id' => $taggable->id,
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag3->id,
            'taggable_id' => $taggable->id,
        ]);
    }

    public function test_it_will_remove_all_taggable_without_key_tags(): void
    {
        $taggable = $this->createTaggable();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $taggable->tags()->attach(
            $tag1->id
        );

        $taggable->tags()->attach(
            $tag2->id
        );

        $this->assertDatabaseCount('taggables', 2);

        $data = [
            'tags' => [],
        ];

        $response = $this->put(
            $this->taggableRoute($taggable).
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_remove_all_taggable_add_key_tags_failed(): void
    {
        $taggable = $this->createTaggable();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $taggable->tags()->attach(
            $tag1->id
        );

        $taggable->tags()->attach(
            $tag2->id
        );

        $this->assertDatabaseCount('taggables', 2);

        $data = [
            'tags' => [
                'en' => [
                    $tag2->id,
                ],
            ],
        ];

        $response = $this->put(
            $this->taggableRoute($taggable).
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);

        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('taggables', 2);
    }

    public function test_it_will_return_empty_tags_for_taggable(): void
    {
        $taggable = $this->createTaggable();

        $response = $this->get(
            $this->taggableRoute($taggable).
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals([], $res->data);
    }

    public function test_it_will_not_update_taggable_tags_with_fake_tag(): void
    {
        $taggable = $this->createTaggable();

        $data = [
            'tags' => [
                99999,
            ],
        ];

        $response = $this->put(
            $this->taggableRoute($taggable).
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);

        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_not_update_taggable_tags_with_wrong_data(): void
    {
        $taggable = $this->createTaggable();

        $data = [
            'wrong' => [
                1,
            ],
        ];

        $response = $this->put(
            $this->taggableRoute($taggable).
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);

        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_return_404_for_fake_taggable(): void
    {
        $response = $this->get(
            $this->fakeTaggableRoute().
            '?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_when_updating_fake_taggable(): void
    {
        $tag = $this->createTag(
            'Shoes',
            'Buty'
        );

        $data = [
            'tags' => [
                $tag->id,
            ],
        ];

        $response = $this->put(
            $this->fakeTaggableRoute().
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_for_fake_tag(): void
    {
        $response = $this->get(
            $this->byTagRouteForId(99999).
            '?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_not_attach_same_tag_to_taggable_twice(): void
    {
        $taggable = $this->createTaggable();

        $tag = $this->createTag(
            'Shoes',
            'Buty'
        );

        $taggable->tags()->attach($tag->id);

        $this->assertDatabaseCount('taggables', 1);

        $this->expectException(QueryException::class);

        $taggable->tags()->attach($tag->id);

        $this->assertDatabaseCount('taggables', 1);
    }

    abstract protected function fakeTaggableRoute(): string;

    abstract protected function byTagRouteForId(int $id): string;
}
