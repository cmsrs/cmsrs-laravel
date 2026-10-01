<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Shop;

use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use App\Models\Cmsrs\Tag\TagTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Services\Cmsrs\Base;

class ProductTagApiTest extends Base
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

    private function createProduct(): Product
    {
        return Product::create([
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

    public function test_it_will_add_tags_to_product_put_docs(): void
    {
        $product = $this->createProduct();

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
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 3);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Product::class,
            'taggable_id' => $product->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag2->id,
            'taggable_type' => Product::class,
            'taggable_id' => $product->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Product::class,
            'taggable_id' => $product->id,
            'lang' => 'pl',
        ]);
    }

    public function test_it_will_return_product_tags_get_docs(): void
    {
        $product = $this->createProduct();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $product->tags()->attach(
            $tag1->id,
            ['lang' => 'en']
        );

        $product->tags()->attach(
            $tag1->id,
            ['lang' => 'pl']
        );

        $product->tags()->attach(
            $tag2->id,
            ['lang' => 'en']
        );

        $response = $this->get(
            'api/products/'.$product->id.'/tags'.
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

    public function test_it_will_return_products_by_tag_get_docs(): void
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        $tag = $this->createTag(
            'Shoes',
            'Buty'
        );

        $product1->tags()->attach(
            $tag->id,
            ['lang' => 'en']
        );

        $product2->tags()->attach(
            $tag->id,
            ['lang' => 'pl']
        );

        $response = $this->get(
            'api/products/tag/'.$tag->id.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertCount(2, $res->data);

        $this->assertEquals(
            $product1->id,
            $res->data[0]->id
        );

        $this->assertEquals(
            'en',
            $res->data[0]->lang
        );

        $this->assertEquals(
            $product2->id,
            $res->data[1]->id
        );

        $this->assertEquals(
            'pl',
            $res->data[1]->lang
        );
    }

    public function test_it_will_replace_product_tags(): void
    {
        $product = $this->createProduct();

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

        $product->tags()->attach(
            $tag1->id,
            ['lang' => 'en']
        );

        $product->tags()->attach(
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
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 1);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag1->id,
            'taggable_id' => $product->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag2->id,
            'taggable_id' => $product->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag3->id,
            'taggable_id' => $product->id,
            'lang' => 'en',
        ]);
    }

    public function test_it_will_remove_all_product_whihout_key_tags(): void
    {
        $product = $this->createProduct();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $product->tags()->attach(
            $tag1->id,
            ['lang' => 'en']
        );

        $product->tags()->attach(
            $tag1->id,
            ['lang' => 'pl']
        );

        $product->tags()->attach(
            $tag2->id,
            ['lang' => 'en']
        );

        $this->assertDatabaseCount('taggables', 3);

        $data = [
            'tags' => [
            ],
        ];

        $response = $this->put(
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_remove_all_product_with_key_tags(): void
    {
        $product = $this->createProduct();

        $tag1 = $this->createTag(
            'Shoes',
            'Buty'
        );

        $tag2 = $this->createTag(
            'Sport',
            'Sport'
        );

        $product->tags()->attach(
            $tag1->id,
            ['lang' => 'en']
        );

        $product->tags()->attach(
            $tag1->id,
            ['lang' => 'pl']
        );

        $product->tags()->attach(
            $tag2->id,
            ['lang' => 'en']
        );

        $this->assertDatabaseCount('taggables', 3);

        $data = [
            'tags' => [
                'en' => [
                ],
            ],
        ];

        $response = $this->put(
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_return_empty_tags_for_product(): void
    {
        $product = $this->createProduct();

        $response = $this->get(
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals([], $res->data);
    }

    public function test_it_will_not_update_product_tags_with_fake_tag(): void
    {
        $product = $this->createProduct();

        $data = [
            'tags' => [
                'en' => [
                    99999,
                ],
            ],
        ];

        $response = $this->put(
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);

        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_not_update_product_tags_with_wrong_data(): void
    {
        $product = $this->createProduct();

        $data = [
            'wrong' => [
                'en' => [
                    1,
                ],
            ],
        ];

        $response = $this->put(
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);

        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_will_return_404_for_fake_product(): void
    {
        $response = $this->get(
            'api/products/99999/tags'.
            '?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_when_updating_fake_product(): void
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
            'api/products/99999/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_for_fake_tag(): void
    {
        $response = $this->get(
            'api/products/tag/99999'.
            '?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_add_tags_to_product_for_unknown_lang(): void
    {
        $product = $this->createProduct();

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
            'api/products/'.$product->id.'/tags'.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseCount('taggables', 2);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Product::class,
            'taggable_id' => $product->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag2->id,
            'taggable_type' => Product::class,
            'taggable_id' => $product->id,
            'lang' => 'en',
        ]);

        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Product::class,
            'taggable_id' => $product->id,
            'lang' => 'de',
        ]);
    }
}
