<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Content;
use App\Models\Cmsrs\Translate;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Page\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TranslateOneLangTest extends Base
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        putenv('LANGS="pl"');
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

    protected function tearDown(): void
    {
        putenv('LANGS="pl"');
        parent::tearDown();
    }

    public function test_menu_translate_wrap_create_ok_2b()
    {
        $menu = (app(MenuService::class))->wrapCreate(['name' => ['en' => 'About cmsRS', 'pl' => 'O cmsRS']]);
        $this->assertNotEmpty($menu->id);

        $countItem = Translate::query()->where('menu_id', $menu->id)->where('column', 'name')->count();
        $this->assertEquals(1, $countItem);
    }

    public function test_page_translate_wrap_create_ok_2()
    {
        $data = $this->getPageTestData();
        unset($data['description']);
        $data['images'] = [
            ['name' => 'phpunittest1.jpg', 'data' => $this->getFixtureBase64('phpunittest1.jpg'), 'alt' => ['en' => 'some desc', 'pl' => 'jakis opis', 'es' => 'Fake']],
            ['name' => 'phpunittest2.jpg', 'data' => $this->getFixtureBase64('phpunittest2.jpg'), 'alt' => ['en' => 'some desc', 'pl' => 'jakis opis', 'es' => 'Fake']],
            ['name' => 'phpunittest2.jpg', 'data' => $this->getFixtureBase64('phpunittest2.jpg'), 'alt' => ['en' => 'some desc2', 'pl' => 'jakis opis2', 'es' => 'Fake2']],
        ];

        $pageService = app(PageService::class);
        $page = $pageService->wrapCreate($data);
        $this->assertNotEmpty($page->id);

        $this->assertEquals(1, Translate::query()->where('page_id', $page->id)->where('column', 'title')->count());
        $this->assertEquals(1, Translate::query()->where('page_id', $page->id)->where('column', 'short_title')->where('lang', 'pl')->count());
        $this->assertEquals(1, Translate::query()->where('page_id', $page->id)->where('column', 'description')->count());
        $this->assertEquals(1, Content::query()->where('page_id', $page->id)->count()); // without DI wrong count

        $this->assertEquals(3, Translate::query()->whereNotNull('image_id')->where('column', 'alt')->count());
    }
}
