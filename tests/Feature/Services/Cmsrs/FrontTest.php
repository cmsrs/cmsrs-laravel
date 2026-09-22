<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Data\Demo;
use App\Models\Cmsrs\Cms\Menu;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Cms\Translate;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Navigation\UrlService;
use App\Services\Cmsrs\Page\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class FrontTest extends Base
{
    use RefreshDatabase;

    private $testData;

    private $testDataMenu;

    private $menuId;

    private $menuObj;

    protected function setUp(): void
    {
        putenv('LANGS="en"');
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

        $this->testDataMenu =
        [
            'name' => ['en' => 'test men7 zółć'],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function setTestData()
    {
        $menu = (app(MenuService::class))->wrapCreate($this->testDataMenu);

        $this->menuObj = $menu;
        $this->menuId = $menu->id;
        $this->assertNotEmpty($this->menuId);

        $this->testData =
        [
            'title' => ['en' => 'page 1 test test slug'],
            'short_title' => ['en' => 'page1'],
            'published' => 1,
            'position' => 7,
            'type' => 'cms',
            'content' => ['en' => 'content test133445'],
            'menu_id' => $this->menuId,
        ];

        (app(PageService::class))->wrapCreate($this->testData);

        $this->assertEquals(1, Menu::All()->count());
        $this->assertEquals(1, Page::All()->count());
        $this->assertEquals(4, Translate::All()->count()); // 1 from menu, 3 from page
    }

    public function test_it_will_get_directly_to_page()
    {

        $testData =
        [
            'title' => ['en' => 'home'],
            'short_title' => ['en' => 'home'],
            'published' => 1,
            'type' => 'home',
            'content' => null,
            'menu_id' => null, // !!!! - contact not related to menu
        ];

        (app(PageService::class))->wrapCreate($testData);

        $count = Page::All()->count();
        $this->assertEquals(1, $count);

        $response1 = $this->get('/home');
        $response1->assertStatus(200);

        $response2 = $this->get('home/orders');
        $response2->assertStatus(404);

        $response3 = $this->get('home/basket');
        $response3->assertStatus(404);

        $response3b = $this->get('home/basketbb');
        $response3b->assertStatus(404);
    }

    public function test_it_will_get_all_pages_one_lang_by_type()
    {
        $langs = ConfigService::arrGetLangsEnv();
        $this->assertEquals(1, count($langs));

        $testDataMenu =
        [
            'name' => ['en' => 'menu test'],
        ];

        $menu = (app(MenuService::class))->wrapCreate($testDataMenu);
        $this->assertNotEmpty($menu->id);

        $pageTypes = ConfigService::arrGetPageTypes();
        $this->assertTrue(count($pageTypes) >= 9);
        foreach ($pageTypes as $page_type) {
            $data = [
                'title' => ['en' => $page_type.' en'],
                'short_title' => ['en' => $page_type],
                'description' => ['en' => 'Description... Needed for google'],
                'published' => 1,
                'commented' => 1,
                'type' => $page_type,
                'content' => ['en' => 'test'],
                'menu_id' => null,
            ];
            $p = (app(PageService::class))->wrapCreate($data);

            $data['menu_id'] = $menu->id;
            if ($page_type !== 'main_page') {
                $p = (app(PageService::class))->wrapCreate($data);
            }
        }
        $pages = Page::All();

        $this->assertEquals(2 * count($pageTypes) - 1, count($pages));
        $in = false;
        foreach ($pages as $page) {

            foreach ($langs as $lang) {
                $url = (app(UrlService::class))->getUrl($page, $lang);
                if (! $url) {
                    $this->assertEquals('inner', $page->type);

                    continue;
                }

                $response = $this->get($url);

                $status = 200;
                // $status = (($page->type === 'login') || ($page->type === 'register') || ($page->type === 'forgot')) ? 302 : 200; // why forgot??
                // if ($page->type == 'shoppingsuccess') {
                //     $status = 404;
                // }

                $response->assertStatus($status);
                $in = true;
            }

        }
        $this->assertTrue($in);
    }

    public function test_it_will_show_contact_on_the_main_page()
    {
        $this->testData =
        [
            'title' => ['en' => 'contact'],
            'short_title' => ['en' => 'contact'],
            'published' => 1,
            'type' => 'contact',
            'content' => null,
            'menu_id' => null, // !!!! - contact not related to menu
        ];

        (app(PageService::class))->wrapCreate($this->testData);

        $count = Page::All()->count();
        $this->assertEquals(1, $count);

        $testMainPage =
        [
            'title' => ['en' => 'cmsRS'],
            'short_title' => ['en' => 'cmsRS'],
            'description' => ['en' => 'cmsRS'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'type' => 'main_page',
            'content' => ['en' => 'main page'],
            'menu_id' => null,
            'page_id' => null,
        ];
        (app(PageService::class))->wrapCreate($testMainPage);
        $this->assertEquals(2, Page::All()->count());

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * maybe it will be useful for sitemap
     * links without: home, login, logout
     */
    public function test_it_will_get_all_pages_status_one_lang()
    {
        ini_set('memory_limit', '1028M');

        $objDemo = new Demo;
        $p = $objDemo->pagesAndMenu(true);

        $this->checkAllPagesByLang($p, 'en', true);

        $products = $objDemo->product($p, true);
        $this->checkProductsPagesByLang($products, 'en');
    }

    public function test_it_will_check_set_up()
    {
        $this->setTestData();
        $response = $this->get('api/pages?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 1);
    }

    public function test_it_will_get_main_page_1()
    {
        $testData2 =
        [
            'title' => ['en' => 'cmsRS'],
            'short_title' => ['en' => 'cmsRS'],
            'description' => ['en' => 'cmsRS'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 1,
            'type' => 'main_page',
            'content' => ['en' => 'main page'],
            'menu_id' => null,
            'page_id' => null,
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);

        $pages = Page::All()->toArray();
        $this->assertEquals(1, count($pages));

        $res = $response->getData();
        $this->assertTrue($res->success);

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_it_will_get_main_page_2()
    {
        $response = $this->get('/');
        $response->assertStatus(404);

        $testData2 =
        [
            'title' => ['en' => 'cmsRS'],
            'short_title' => ['en' => 'cmsRS'],
            'description' => ['en' => 'cmsRS'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 1,
            'type' => 'main_page',
            'content' => ['en' => 'main page'],
            'menu_id' => null,
            'page_id' => null,
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);

        $pages = Page::All()->toArray();
        $this->assertEquals(1, count($pages));

        $res = $response->getData();
        $this->assertTrue($res->success);

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_it_will_get_cms_page0()
    {
        $this->setTestData();
        $title = $this->testData['title']['en'];
        $pageSlug = Str::slug($title);

        $menuName = $this->testDataMenu['name']['en'];
        $menuSlug = Str::slug($menuName);

        $p0 = Page::query()->where('menu_id', $this->menuId)->get()->first();
        $this->assertNotEmpty($p0);

        $url = (app(UrlService::class))->getUrl($p0, 'en');

        $response1 = $this->get($url);
        $response1->assertStatus(200);
    }

    public function test_it_will_get_cms_page1()
    {
        $this->setTestData();
        $title = $this->testData['title']['en'];
        $pageSlug = Str::slug($title);

        $menuName = $this->testDataMenu['name']['en'];
        $menuSlug = Str::slug($menuName);

        $p0 = Page::query()->where('menu_id', $this->menuId)->get(); // ->toArray();
        $this->assertEquals(1, $p0->count());

        // not working - see this it_will_get_cms_page0 (this work)
        // $response = $this->get('/c/'.$menuSlug.'/'.$pageSlug);
        // $response->assertStatus(404);

        $testData2 =
        [
            'title' => ['en' => 'cmsRS'],
            'short_title' => ['en' => 'cmsRS'],
            'description' => ['en' => 'cmsRS'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 1,
            'type' => 'cms',
            'content' => ['en' => 'main page'],
            'menu_id' => $this->menuId,
            'page_id' => null,
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);

        $res = $response->getData();
        $this->assertTrue($res->success);

        $p = Page::query()->where('menu_id', $this->menuId)->get(); // ->toArray();
        $this->assertEquals(2, $p->count());

        $i = 0;
        foreach ($p as $pp) {
            $url0 = (app(UrlService::class))->getUrl($pp, 'en');
            $response = $this->get($url0);
            $response->assertStatus(200);
            $i++;
        }
        $this->assertEquals(2, $i);

        $responseB = $this->get('/'.$menuSlug);
        $responseB->assertStatus(404);
    }

    public function test_it_will_get_in_page()
    {
        $pPrivacy = [
            'title' => ['en' => 'Privacy policy', 'pl' => 'polityka prywatnosci'],
            'short_title' => ['en' => 'Privacy policy', 'pl' => 'polityka prywatnosci'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'polityka prywatnosci'],
            'published' => 1,
            'commented' => 0,
            'type' => 'privacy_policy',
            'content' => ['en' => 'politics ....', 'pl' => 'polityka prywatnosci'],
            'images' => [
            ],
        ];

        $p = (app(PageService::class))->wrapCreate($pPrivacy);
        $this->assertNotEmpty($p->id);

        $lang = 'en';
        $url = (app(UrlService::class))->getUrl($p, $lang);
        $this->assertNotEmpty($url);
        $response1 = $this->get($url);
        $response1->assertStatus(200);
    }
}
