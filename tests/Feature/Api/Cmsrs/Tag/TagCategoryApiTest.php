<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Tag;

use App\Enums\Cmsrs\Tag\TagCategoryType;
use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use App\Models\Cmsrs\Tag\TagCategoryTranslation;
use App\Models\Cmsrs\Tag\TagTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Services\Cmsrs\Base;

class TagCategoryApiTest extends Base
{
    use RefreshDatabase;

    private array $testData;

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

        $this->testData = [
            'entity_type' => TagCategoryType::PRODUCT->value,
            'name' => [
                'en' => 'Product type',
                'pl' => 'Typ produktu',
            ],
        ];
    }

    public function test_it_will_add_tag_category_post_docs(): void
    {
        $this->assertDatabaseCount('tag_categories', 0);
        $this->assertDatabaseCount('tag_category_translations', 0);

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $this->testData
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals(1, TagCategory::count());
        $this->assertDatabaseCount('tag_categories', 1);
        $this->assertDatabaseCount('tag_category_translations', 2);

        $category = TagCategory::first();

        $this->assertNotNull($category);

        $this->assertDatabaseHas('tag_categories', [
            'id' => $category->id,
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        $this->restDoc->add(
            'POST',
            'api/tag-categories?token=$token',
            $this->testData,
            $res,
        );
    }

    public function test_it_will_return_all_tag_categories_get_docs(): void
    {
        $category1 = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);
        $category2 = TagCategory::create([
            'entity_type' => TagCategoryType::PAGE->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category1->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category1->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category2->id,
            'lang' => 'en',
            'value' => 'Topic',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category2->id,
            'lang' => 'pl',
            'value' => 'Tytul',
        ]);

        $response = $this->get(
            'api/tag-categories?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);
        $this->assertCount(2, $res->data);

        $this->assertEquals(
            'Product type',
            $res->data[0]->name->en
        );

        $this->assertEquals(
            'product',
            $res->data[0]->entity_type
        );

        $this->assertEquals(
            'Typ produktu',
            $res->data[0]->name->pl
        );

        $this->assertEquals(
            'page',
            $res->data[1]->entity_type
        );

        $this->assertEquals(
            'Topic',
            $res->data[1]->name->en
        );

        $this->assertEquals(
            'Tytul',
            $res->data[1]->name->pl
        );

        $this->restDoc->add(
            'GET',
            'api/tag-categories?token=$token',
            null,
            $res,
        );
    }

    public function test_it_will_return_one_tag_category_get_docs(): void
    {
        $category = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        $response = $this->get(
            'api/tag-categories/'.$category->id.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals(
            $category->id,
            $res->data->id
        );

        $this->assertEquals(
            'Product type',
            $res->data->name->en
        );

        $this->assertEquals(
            'Typ produktu',
            $res->data->name->pl
        );

        $this->assertEquals(
            'product',
            $res->data->entity_type
        );

        $this->restDoc->add(
            'GET',
            'api/tag-categories/$category_id?token=$token',
            null,
            $res,
        );

    }

    public function test_it_will_update_tag_category_put_docs(): void
    {
        $category = TagCategory::create([
            'entity_type' => TagCategoryType::PAGE->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Old name',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Stara nazwa',
        ]);

        $data = [
            'entity_type' => TagCategoryType::PRODUCT->value,
            'name' => [
                'en' => 'Product type updated',
                'pl' => 'Typ produktu zmieniony',
            ],
        ];

        $response = $this->put(
            'api/tag-categories/'.$category->id.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseHas('tag_categories', [
            'id' => $category->id,
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type updated',
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu zmieniony',
        ]);

        $this->assertDatabaseMissing('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Old name',
        ]);

        $this->restDoc->add(
            'PUT',
            'api/tag-categories/$category_id?token=$token',
            $data,
            $res,
        );

    }

    public function test_it_will_delete_tag_category_delete_docs(): void
    {
        $category = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        $this->assertEquals(1, TagCategory::count());
        $this->assertDatabaseCount('tag_categories', 1);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        $response = $this->delete(
            'api/tag-categories/'.$category->id.
            '?token='.$this->token
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals(0, TagCategory::count());
        $this->assertDatabaseCount('tag_categories', 0);
        $this->assertDatabaseCount('tag_category_translations', 0);

        $this->assertDatabaseMissing('tag_categories', [
            'id' => $category->id,
        ]);

        $this->assertDatabaseMissing('tag_category_translations', [
            'tag_category_id' => $category->id,
        ]);

        $this->restDoc->add(
            'DELETE',
            'api/tag-categories/$category_id?token=$token',
            null,
            $res,
        );
    }

    public function test_it_will_not_add_tag_category_with_empty_name(): void
    {
        $data = [
            'entity_type' => TagCategoryType::PRODUCT->value,
            'name' => [
                'en' => '',
                'pl' => 'Typ produktu',
            ],
        ];

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tag_categories', 0);
    }

    public function test_it_will_not_add_tag_category_when_language_is_missing(): void
    {
        $data = [
            'entity_type' => TagCategoryType::PRODUCT->value,
            'name' => [
                'en' => 'Product type',
            ],
        ];

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tag_categories', 0);
    }

    public function test_it_will_not_add_tag_category_when_key_is_wrong(): void
    {
        $data = [
            'entity_type' => TagCategoryType::PRODUCT->value,
            'name_wrong' => [
                'en' => 'Product type',
            ],
        ];

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tag_categories', 0);
    }

    public function test_it_will_not_add_tag_category_with_duplicate_name(): void
    {
        $category = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        $data = [
            'entity_type' => TagCategoryType::PRODUCT->value,
            'name' => [
                'en' => 'Product type',
                'pl' => 'Inna nazwa',
            ],
        ];

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tag_categories', 1);
    }

    public function test_it_will_not_update_tag_category_with_duplicate_name(): void
    {
        $category = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        $categorySecond = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $categorySecond->id,
            'lang' => 'en',
            'value' => 'Size',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $categorySecond->id,
            'lang' => 'pl',
            'value' => 'Rozmiar',
        ]);

        $data = [
            'name' => [
                'en' => 'Size',
                'pl' => 'Inna nazwa',
            ],
        ];

        $response = $this->put(
            'api/tag-categories/'.$category->id.'?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tag_categories', 2);
    }

    public function test_it_will_return_404_for_fake_tag_category(): void
    {
        $response = $this->get(
            'api/tag-categories/99999?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_return_404_when_deleting_fake_tag_category(): void
    {
        $response = $this->delete(
            'api/tag-categories/99999?token='.$this->token
        );

        $response->assertStatus(404);
    }

    public function test_it_will_delete_category_with_tags(): void
    {
        $category = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        $tag = Tag::create([
            'tag_category_id' => $category->id,
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

        $this->assertDatabaseCount('tag_categories', 1);
        $this->assertDatabaseCount('tag_category_translations', 2);
        $this->assertDatabaseCount('tags', 1);
        $this->assertDatabaseCount('tag_translations', 2);

        $response = $this->delete(
            'api/tag-categories/'.$category->id.
            '?token='.$this->token
        );

        $this->assertDatabaseCount('tag_categories', 0);
        $this->assertDatabaseCount('tag_category_translations', 0);
        $this->assertDatabaseCount('tags', 0);
        $this->assertDatabaseCount('tag_translations', 0);

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseMissing('tag_categories', [
            'id' => $category->id,
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('tag_translations', [
            'tag_id' => $tag->id,
        ]);
    }

    public function test_it_will_add_tag_category_with_additional_language(): void
    {
        $this->testData['name']['fr'] = 'Type de produit'; // Adding a language that is not in the LANGS environment variable

        $this->assertDatabaseCount('tag_categories', 0);
        $this->assertDatabaseCount('tag_category_translations', 0);

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $this->testData
        );

        $response->assertStatus(200);

        $this->assertDatabaseCount('tag_categories', 1);
        $this->assertDatabaseCount('tag_category_translations', 2);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals(1, TagCategory::count());

        $category = TagCategory::first();

        $this->assertNotNull($category);

        $this->assertDatabaseHas('tag_categories', [
            'entity_type' => TagCategoryType::PRODUCT->value,
            'id' => $category->id,
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type',
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu',
        ]);

        $this->assertDatabaseMissing('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'fr',
            'value' => 'Type de produit',
        ]);
    }

    public function test_it_will_update_tag_category_with_additional_language(): void
    {
        $category = TagCategory::create([
            'entity_type' => TagCategoryType::PRODUCT->value,
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Old name',
        ]);

        TagCategoryTranslation::create([
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Stara nazwa',
        ]);

        $data = [
            'entity_type' => TagCategoryType::PAGE->value,
            'name' => [
                'en' => 'Product type updated',
                'pl' => 'Typ produktu zmieniony',
                'fr' => 'Fr Product type updated',
            ],
        ];

        $response = $this->put(
            'api/tag-categories/'.$category->id.
            '?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseHas('tag_categories', [
            'id' => $category->id,
            'entity_type' => TagCategoryType::PAGE->value,
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Product type updated',
        ]);

        $this->assertDatabaseHas('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'pl',
            'value' => 'Typ produktu zmieniony',
        ]);

        $this->assertDatabaseMissing('tag_category_translations', [
            'tag_category_id' => $category->id,
            'lang' => 'en',
            'value' => 'Old name',
        ]);
    }

    public function test_it_will_add_product_tag_category(): void
    {
        $data = [
            'entity_type' => 'product',
            'name' => [
                'en' => 'Product type',
                'pl' => 'Typ produktu',
            ],
        ];

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertDatabaseHas('tag_categories', [
            'entity_type' => 'product',
        ]);
    }

    public function test_it_will_add_page_tag_category(): void
    {
        $data = [
            'entity_type' => 'page',
            'name' => [
                'en' => 'Topics',
                'pl' => 'Tematy',
            ],
        ];

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('tag_categories', [
            'entity_type' => 'page',
        ]);
    }

    public function test_it_will_not_add_tag_category_with_invalid_type(): void
    {
        $data = [
            'entity_type' => 'something',
            'name' => [
                'en' => 'Test',
                'pl' => 'Test',
            ],
        ];

        $response = $this->post(
            'api/tag-categories?token='.$this->token,
            $data
        );

        $response->assertStatus(200);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);

        $this->assertDatabaseCount('tag_categories', 0);
    }
}
