<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Tag;

use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Services\Cmsrs\Base;

abstract class TaggableCreateApiCase extends Base
{
    use RefreshDatabase;

    abstract protected function createRoute(): string;

    abstract protected function createData(array $extra = []): array;

    abstract protected function taggableClass(): string;

    abstract protected function entityType(): string;

    abstract protected function tableName(): string;

    abstract protected function idResponsePath(): string;

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

    public function test_it_creates_a_taggable_with_a_tag_of_its_entity_type_docs(): void
    {
        $category = TagCategory::create(['entity_type' => $this->entityType()]);
        $tags = [
            Tag::create(['tag_category_id' => $category->id]),
            Tag::create(['tag_category_id' => $category->id]),
        ];
        $tagIds = array_map(fn (Tag $tag): int => $tag->id, $tags);

        $dataInput = $this->createData(['tags' => $tagIds]);
        $response = $this->post(
            $this->createRoute().'?token='.$this->token,
            $this->createData($dataInput)
        );

        $response->assertOk();
        $this->assertErrorMatchesSuccess($response);
        $this->assertSame($tagIds, $response->json('data.data.tags'));

        $this->assertDatabaseCount('taggables', count($tags));
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_type' => $this->taggableClass(),
                'taggable_id' => $response->json($this->idResponsePath()),
            ]);
        }

        $this->restDoc->add(
            'POST',
            'api/pages_or_products?token=token',
            $dataInput,
            $response->getData(),
        );

    }

    public function test_it_creates_a_taggable_without_tags_when_the_key_is_omitted(): void
    {
        $response = $this->post(
            $this->createRoute().'?token='.$this->token,
            $this->createData()
        );

        $response->assertOk();
        $this->assertErrorMatchesSuccess($response);
        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_does_not_create_a_taggable_with_an_unknown_tag(): void
    {
        $response = $this->post(
            $this->createRoute().'?token='.$this->token,
            $this->createData(['tags' => [999999]])
        );

        $response->assertOk();
        $this->assertErrorMatchesFailure($response);
        $this->assertDatabaseCount($this->tableName(), 0);
        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_does_not_create_a_taggable_with_a_tag_of_another_entity_type(): void
    {
        $wrongEntityType = $this->entityType() === 'page' ? 'product' : 'page';
        $category = TagCategory::create(['entity_type' => $wrongEntityType]);
        $tag = Tag::create(['tag_category_id' => $category->id]);

        $response = $this->post(
            $this->createRoute().'?token='.$this->token,
            $this->createData(['tags' => [$tag->id]])
        );

        $response->assertOk();
        $this->assertErrorMatchesFailure($response);
        $this->assertDatabaseCount($this->tableName(), 0);
        $this->assertDatabaseCount('taggables', 0);
    }

    private function assertErrorMatchesSuccess(TestResponse $response): void
    {
        $response->assertJsonPath('success', true);
        $response->assertJsonMissingPath('error');
    }

    private function assertErrorMatchesFailure(TestResponse $response): void
    {
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error', fn ($error) => ! empty($error));
    }
}
