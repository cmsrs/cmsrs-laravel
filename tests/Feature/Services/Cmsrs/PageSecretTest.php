<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Page\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageSecretTest extends Base
{
    use RefreshDatabase;

    private $testData;

    private $testDataMenu;

    private $menuId;

    private $menuObj;

    private $objPage;

    private $apiSecret;

    protected function setUp(): void
    {
        $this->apiSecret = '8761';
        putenv('LANGS="en"');
        putenv('API_SECRET="'.$this->apiSecret.'"');
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

        $this->testData =
        [
            'title' => ['en' => 'page 1 test'],
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

        $this->testDataMenu =
        [
            'name' => ['en' => 'test men7'],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function setTestData()
    {
        $this->objPage = (app(PageService::class))->wrapCreate($this->testData);

        $menu = (app(MenuService::class))->wrapCreate($this->testDataMenu);

        $this->menuObj = $menu->all()->first();
        $this->menuId = $this->menuObj->id;
    }

    public function test_it_will_add_main_page()
    {
        $testData2 =
      [
          'title' => ['en' => 'test p2xx'],
          'short_title' => ['en' => 'p22'],
          'description' => ['en' => 'test1234'],
          'published' => 1,
          'commented' => 0,
          'after_login' => 1,
          'type' => 'main_page',
          'content' => ['en' => 'aaa test1234234'],
          'menu_id' => null, // it must be null for type main_page
          'page_id' => null, // it must be null for type main_page
      ];
        $apiUrl = 'api/'.$this->apiSecret.'/pages?token='.$this->token;
        $this->assertNotEmpty($this->apiSecret);
        $response = $this->post($apiUrl, $testData2);

        $res = $response->getData();
        $this->assertTrue($res->success);

        $pages = Page::all()->toArray();
        $this->assertEquals(1, count($pages));
        $this->assertEquals('main_page', $pages[0]['type']);
    }

    public function test_it_will_delete_page()
    {
        $this->assertNotEmpty($this->apiSecret);
        $this->setTestData();
        $responseAll = $this->get('api/'.$this->apiSecret.'/pages?token='.$this->token);
        $resAll = $responseAll->getData();
        $this->assertNotEmpty($resAll->data);
        $id = $resAll->data[0]->id;
        $this->assertNotEmpty($id);

        $response0 = $this->delete('api/'.$this->apiSecret.'/pages/'.$id.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $responseAllAfter = $this->get('api/'.$this->apiSecret.'/pages?token='.$this->token);
        $resAllAfter = $responseAllAfter->getData();
        $this->assertEmpty($resAllAfter->data);
    }
}
