<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Cms;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Services\Cmsrs\Base;

class PageApiTest extends Base
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

    public function test_it_creates_a_page_with_tags(): void
    {
        $category = TagCategory::create();
        $tag1 = Tag::create(['tag_category_id' => $category->id]);
        $tag2 = Tag::create(['tag_category_id' => $category->id]);

        $data = $this->pageData([
            'tags' => [$tag1->id, $tag2->id],
        ]);

        $response = $this->post('api/pages?token='.$this->token, $data);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $pageId = $response->json('data.pageId');
        $this->assertDatabaseCount('taggables', 2);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag1->id,
            'taggable_type' => Page::class,
            'taggable_id' => $pageId,
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag2->id,
            'taggable_type' => Page::class,
            'taggable_id' => $pageId,
        ]);
        $this->assertSame([$tag1->id, $tag2->id], $response->json('data.data.tags'));
    }

    public function test_it_creates_a_page_without_tags_when_the_key_is_omitted(): void
    {
        $response = $this->post('api/pages?token='.$this->token, $this->pageData());

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseCount('taggables', 0);
    }

    public function test_it_does_not_create_a_page_with_an_unknown_tag(): void
    {
        $response = $this->post(
            'api/pages?token='.$this->token,
            $this->pageData(['tags' => [999999]])
        );

        $response->assertOk();
        $response->assertJsonPath('success', false);
        $this->assertDatabaseCount('pages', 0);
        $this->assertDatabaseCount('taggables', 0);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function pageData(array $extra = []): array
    {
        return array_merge([
            'title' => ['en' => 'Page title', 'pl' => 'Tytuł strony'],
            'short_title' => ['en' => 'Page', 'pl' => 'Strona'],
            'description' => ['en' => 'Description', 'pl' => 'Opis'],
            'published' => 0,
            'commented' => 0,
            'after_login' => 0,
            'type' => 'contact',
            'content' => ['en' => 'Content', 'pl' => 'Treść'],
            'menu_id' => '',
            'page_id' => '',
        ], $extra);
    }
}
