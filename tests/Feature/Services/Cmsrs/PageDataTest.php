<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Menu;
use App\Services\Cmsrs\Cms\MenuService;
use App\Services\Cmsrs\Cms\Page\PageDataService;
use App\Services\Cmsrs\Cms\Page\PageService;
use App\Services\Cmsrs\ConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageDataTest extends Base
{
    use RefreshDatabase;

    const STR_DESC_IMG1 = 'description img1';

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

    private $name1;

    private $name2;

    private $file2;

    private $pageId;

    private $arrPageId;

    private $testImgData;

    private $pagesData;

    private $pageData;

    private $testProductData;

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

        $this->name1 = 'phpunittest1.jpg';
        $file1 = $this->getFixtureBase64($this->name1);

        $this->name2 = 'phpunittest2.jpg';
        $file2 = $this->getFixtureBase64($this->name2);
        $this->file2 = $file2;

        $images = [
            ['name' => $this->name1, 'data' => $file1, 'alt' => ['en' => self::STR_DESC_IMG1]],
            ['name' => $this->name2, 'data' => $file2], // , 'alt' => ['en' => 'description img2' ]]
        ];

        $this->testImgData =
        [
            'title' => ['en' => 'test p2'],
            'short_title' => ['en' => 'p22'],
            'description' => ['en' => 'test1234'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'type' => 'cms', // this page will be useful for create product   'contact',
            'content' => ['en' => 'lorem ipsum'],
            'menu_id' => null,
            'page_id' => null,
            'images' => $images,
        ];

        $this->strTestMenuName = 'test men7';
        $this->testDataMenu =
        [
            'name' => ['en' => $this->strTestMenuName],
        ];

        // $this->strTestTitle = 'page 1 test';
        // $this->testData =
        // [
        //     'title' => ['en' => $this->strTestTitle],
        //     'short_title' => ['en' => 'page1'],
        //     'description' => ['en' => 'this page: test desc ...'],
        //     'published' => 1,
        //     'commented' => 1,
        //     'after_login' => 0,
        //     'type' => 'cms',
        //     'content' => ['en' => 'content test133445'],
        //     'menu_id' => null,
        //     'page_id' => null,
        // ];

        (new ConfigService)->createFileCacheEnableIfNotExist();
    }

    protected function tearDown(): void
    {
        (new ConfigService)->deleteFileCacheEnableIfExist();
        parent::tearDown();
    }

    private function setTestData()
    {
        $menu = (app(MenuService::class))->wrapCreate($this->testDataMenu);

        $this->assertEquals(1, Menu::all()->count());
        $this->menuObj = Menu::all()->first();

        $this->menuId = $this->menuObj->id;
        $this->assertNotEmpty($this->menuId);
        $this->assertEquals($menu->id, $this->menuId);

        $this->testImgData['menu_id'] = $this->menuId;
        $this->objPage = (app(PageService::class))->wrapCreate($this->testImgData);
        $this->assertNotEmpty($this->objPage->id);
    }

    public function test_it_uses_cache_for_page_with_images_by_short_title()
    {

        Cache::flush();
        $this->setTestData(); //  prepareTestPage();

        $service = app(PageDataService::class);

        $shortTitle = $this->testImgData['short_title']['en'];

        DB::flushQueryLog();
        DB::enableQueryLog();

        // 1st call → DB hit
        $result1 = $service->getPageDataImagesByShortTitleCache($shortTitle);
        $queriesAfterFirst = count(DB::getQueryLog());
        $this->assertTrue($queriesAfterFirst > 0);
        // dump('first='. $queriesAfterFirst);

        DB::flushQueryLog(); // 🔥 reset między wywołaniami

        // 2nd call → should be cache
        $result2 = $service->getPageDataImagesByShortTitleCache($shortTitle);
        $queriesAfterSecond = count(DB::getQueryLog());
        // dump('second='. $queriesAfterSecond);

        $this->assertTrue($queriesAfterFirst > $queriesAfterSecond);

        $this->assertEquals($result1, $result2);

        // 🔥 KLUCZOWE
        $this->assertEquals(
            0,
            $queriesAfterSecond,
            'Second call should not hit database'
        );
    }

    public function test_it_uses_cache_for_page_by_short_title()
    {

        Cache::flush();
        $this->setTestData(); //  prepareTestPage();

        $service = app(PageDataService::class);

        $shortTitle = $this->testImgData['short_title']['en'];

        DB::flushQueryLog();
        DB::enableQueryLog();

        // 1st call → DB hit
        $result1 = $service->getPageDataByShortTitleCache($shortTitle);
        $queriesAfterFirst = count(DB::getQueryLog());
        $this->assertTrue($queriesAfterFirst > 0);
        // dump('first='. $queriesAfterFirst);

        DB::flushQueryLog(); // 🔥 reset między wywołaniami

        // 2nd call → should be cache
        $result2 = $service->getPageDataByShortTitleCache($shortTitle);
        $queriesAfterSecond = count(DB::getQueryLog());
        // dump('second='. $queriesAfterSecond);

        $this->assertTrue($queriesAfterFirst > $queriesAfterSecond);

        $this->assertEquals($result1, $result2);

        // 🔥 KLUCZOWE
        $this->assertEquals(
            0,
            $queriesAfterSecond,
            'Second call should not hit database'
        );
    }

    public function test_it_uses_cache_for_page_by_slugs()
    {
        $this->setTestData();
        $menus = app(MenuService::class)->getMenu();

        $menuSlug = Str::slug($this->strTestMenuName, '-');
        $titleSlug = Str::slug($this->testImgData['title']['en'], '-');
        $lang = 'en';

        Cache::flush();

        $service = app(PageDataService::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        // 1st call → DB hit
        $result1 = $service->getPageBySlugCache($menus, $menuSlug, $titleSlug, $lang);

        $queriesAfterFirst = count(DB::getQueryLog());
        // $this->assertTrue($queriesAfterFirst > 0);

        DB::flushQueryLog(); // 🔥 reset między wywołaniami

        // 2nd call → should be cache
        $result2 = $service->getPageBySlugCache($menus, $menuSlug, $titleSlug, $lang);
        $queriesAfterSecond = count(DB::getQueryLog());

        $this->assertTrue($queriesAfterFirst > $queriesAfterSecond);

        $this->assertEquals($result1, $result2);
        $this->assertEquals($this->objPage->id, $result2->id);
        $this->assertEquals($result1->id, $result2->id);

        // 🔥 KLUCZOWE
        $this->assertEquals(
            0,
            $queriesAfterSecond,
            'Second call should not hit database'
        );
    }
}
