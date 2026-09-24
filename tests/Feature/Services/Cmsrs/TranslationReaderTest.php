<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\Cms\MenuService;
use App\Services\Cmsrs\Cms\Page\PageService;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Translation\TranslationReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationReaderTest extends Base
{
    use RefreshDatabase;

    const STR_PARENT_TWO = 'parent2 p2';

    const STR_PARENT_TREE = 'parent3 p5';

    const STR_CHILD_ONE = 'child1 p3';

    const STR_CHILD_TWO = 'child2 p4';

    private $testData;

    private $testDataMenu;

    private $menuId;

    private $menuObj;

    private $objPage;

    private $strTestTitle;

    private $strTestMenuName;

    protected function setUp(): void
    {
        putenv('LANGS="en"');
        putenv('API_SECRET=""');
        putenv('CURRENCY="USD"');
        putenv('CACHE_ENABLE=true');
        putenv('CACHE_ENABLE_FILE="app/cache_enable_test.txt"');
        putenv('DEMO_STATUS=false');
        putenv('IS_SHOP=true');
        putenv('IS_LOGIN=true');
        putenv('IS_REGISTER=true');
        putenv('IS_HEADLESS=false');
        putenv('IS_SSR=true');

        parent::setUp();

        $this->createUser();
        $this->strTestTitle = 'page 1 test';

        $this->testData =
        [
            'title' => ['en' => $this->strTestTitle],
            'short_title' => ['en' => 'page1'],
            'description' => ['en' => 'this page: test desc ...'],
            'published' => 1,
            'commented' => 1,
            'after_login' => 0,
            'type' => 'cms',
            'content' => ['en' => 'content test133445'],
            'menu_id' => null,
            'page_id' => null,
        ];

        $this->strTestMenuName = 'test men7';
        $this->testDataMenu =
        [
            'name' => ['en' => $this->strTestMenuName],
        ];
        (new ConfigService)->createFileCacheEnableIfNotExist();
    }

    protected function tearDown(): void
    {
        (new ConfigService)->deleteFileCacheEnableIfExist();
        parent::tearDown();
    }

    private function setTestData()
    {
        $this->objPage = (app(PageService::class))->wrapCreate($this->testData);

        $menu = (app(MenuService::class))->wrapCreate($this->testDataMenu);

        $this->menuObj = $menu->all()->first();
        $this->menuId = $this->menuObj->id;
    }

    public function test_it_will_translates_by_column_and_lang_cache()
    {
        Cache::flush();
        // $this->markTestSkipped('dont use getAllTranslate, therefore we skip this test' );
        $this->setTestData();
        $responseAll = $this->get('api/pages?token='.$this->token);
        $resAll = $responseAll->getData();
        $id = $resAll->data[0]->id;

        $this->assertNotEmpty($id);

        $page = Page::All();
        $this->assertEquals($page->count(), 1);

        $pageObj = $page->first();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $r1 = app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'title', 'en');
        $this->assertEquals($this->testData['title']['en'], $r1);
        $queriesAfterFirst = count(DB::getQueryLog());
        $this->assertTrue($queriesAfterFirst > 0);
        // dump('first='. $queriesAfterFirst);

        DB::flushQueryLog(); // reset między wywołaniami

        // 2nd call → should be cache
        $r2 = app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'title', 'en');
        $this->assertEquals($this->testData['title']['en'], $r2);
        $queriesAfterSecond = count(DB::getQueryLog());
        // dump('second='. $queriesAfterSecond);

        $this->assertEquals($r1, $r2);
        $this->assertTrue($queriesAfterFirst > $queriesAfterSecond);

        //  KLUCZOWE
        $this->assertEquals(
            0,
            $queriesAfterSecond,
            'Second call should not hit database'
        );

    }
}
