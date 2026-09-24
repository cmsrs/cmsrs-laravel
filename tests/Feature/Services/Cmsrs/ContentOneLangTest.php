<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Content;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Page\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentOneLangTest extends Base
{
    use RefreshDatabase;

    public $numOfLangs;

    protected function setUp(): void
    {
        putenv('LANGS="pl"'); // tudoalem 1 jezyk!!!!!!!!!!!!!!!!!!!!
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

        $numOfLangs = app(ConfigService::class)->arrGetLangs();
        $this->assertEquals(1, count($numOfLangs));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_page_content_wrap_create_ok_one_lang_mock()
    {
        $data = $this->getPageTestData();

        $pageService = app(PageService::class);
        $p = $pageService->wrapCreate($data);
        $this->assertNotEmpty($p->id);

        $this->assertEquals(1, Content::query()->where('page_id', $p->id)->count());
    }
}
