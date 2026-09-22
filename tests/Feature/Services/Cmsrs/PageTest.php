<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Menu;
use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Navigation\UrlService;
use App\Services\Cmsrs\Page\PageDataService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\Translation\TranslationReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PageTest extends Base
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

    public function test_it_will_get_data_page_by_short_title()
    {
        $p1 = (app(PageService::class))->wrapCreate($this->testData);
        $this->assertNotEmpty($p1->id);

        $testData =
        [
            'title' => ['en' => 'inner title'],
            'short_title' => ['en' => 'inner short_title'],
            'type' => 'inner',
            'content' => ['en' => 'content test4333 inner'],
            'published' => 1,
        ];

        $objPage = (app(PageService::class))->wrapCreate($testData);
        $this->assertNotEmpty($objPage->id);

        $testData2 =
        [
            'title' => ['en' => 'inner title 2'],
            'short_title' => ['en' => 'inner short_title 2111'],
            'type' => 'inner',
            'content' => ['en' => 'content test4333 inner 22'],
            // 'content' =>  [],
            'published' => 1, // true //todo - this condition must be !
        ];

        $objPage2 = (app(PageService::class))->wrapCreate($testData2);
        $this->assertNotEmpty($objPage2->id);

        $shortTitle = $testData2['short_title']['en'];
        $content = (app(PageDataService::class))->getPageDataByShortTitleCache($shortTitle);

        $this->assertEquals($testData2['content']['en'], $content);

        $title = (app(PageDataService::class))->getPageDataByShortTitleCache($shortTitle, 'title');

        $this->assertEquals($testData2['title']['en'], $title);

        $url = (app(PageDataService::class))->getPageDataByShortTitleCache($shortTitle, 'url');

        $this->assertEmpty($url); // because it is inner page

        $shortTitle = $this->testData['short_title']['en'];
        $content = (app(PageDataService::class))->getPageDataByShortTitleCache($shortTitle);
        $this->assertEquals($this->testData['content']['en'], $content);

        $title = (app(PageDataService::class))->getPageDataByShortTitleCache($shortTitle, 'title');
        $this->assertEquals($this->testData['title']['en'], $title);

        $url = (app(PageDataService::class))->getPageDataByShortTitleCache($shortTitle, 'url');
        $this->assertNotEmpty($url);

        $response = $this->get('api/pages?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $lastItem = $res->data[count($res->data) - 1];
        $this->assertEquals('inner', $lastItem->type);
        $this->assertEquals($testData2['short_title']['en'], $lastItem->short_title->en);
    }

    public function test_it_will_add_inner_page_and_show()
    {
        $testData =
        [
            'title' => ['en' => 'inner title'],
            'short_title' => ['en' => 'inner short_title234333 rs'],
            'type' => 'inner',
            // 'content' =>  ['en' => 'content test4333 inner'],
            'content' => [],
            // 'published' => true
        ];
        $response = $this->post('api/pages?token='.$this->token, $testData);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $this->assertEquals($testData['short_title']['en'], $res2->data[0]->short_title->en);
        $this->assertEquals('inner', $res2->data[0]->type);
        // dd($res2);
    }

    public function test_it_will_save_inner_type_page()
    {
        $testData =
        [
            'title' => ['en' => 'inner title'],
            'short_title' => ['en' => 'inner short_title'],
            'type' => 'inner',
            'content' => ['en' => 'content test4333 inner'],
        ];

        $objPage = (app(PageService::class))->wrapCreate($testData);
        $this->assertNotEmpty($objPage->id);

        $content = $objPage->contents->first();
        $this->assertEquals($testData['content']['en'], $content->value);

        $contentValue = (app(PageDataService::class))->getContentInnerPageByIdCache($objPage->id);
        $this->assertEquals($testData['content']['en'], $contentValue);
    }

    public function test_it_will_check_position_page_not_related_to_menu()
    {
        $p1 = (app(PageService::class))->wrapCreate($this->testData);
        $this->testData['title'] = ['en' => 'uniq'];
        $this->testData['type'] = 'clear';
        $p2 = (app(PageService::class))->wrapCreate($this->testData);

        $this->assertEquals(2, Page::All()->count());
        $page1 = Page::query()
            ->orderBy('position', 'asc')
            ->first()
            ->toArray();

        $this->assertEquals('cms', $page1['type']);

        $res2a = $this->patch('api/pages/position/up/'.$page1['id'].'?token='.$this->token);
        $res22a = $res2a->getData();
        $this->assertTrue($res22a->success);

        $this->assertEquals(2, Page::All()->count());
        $page11 = Page::query()
            ->orderBy('position', 'asc')
            ->first()
            ->toArray();

        $this->assertEquals('clear', $page11['type']);
    }

    public function test_it_will_check_uniq_title_by_empty_menu_add_page()
    {
        // page not belong to menu
        $p1 = (app(PageService::class))->wrapCreate($this->testData);
        $this->assertNotEmpty($p1->id);

        $response = $this->post('api/pages?token='.$this->token, $this->testData);
        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
        $this->assertEquals(1, Page::All()->count());
    }

    public function test_it_will_check_uniq_title_by_menu_add_page()
    {
        $menu1 = (app(MenuService::class))->wrapCreate($this->testDataMenu);
        $menu2 = (app(MenuService::class))->wrapCreate(['name' => ['en' => $this->strTestTitle]]);

        $this->assertNotEmpty($menu1->id);
        $this->assertNotEmpty($menu2->id);
        $this->testData['menu_id'] = $menu1->id;
        // page belong to menu
        $p1 = (app(PageService::class))->wrapCreate($this->testData);
        $this->assertNotEmpty($p1->id);

        $response = $this->post('api/pages?token='.$this->token, $this->testData);
        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
        $this->assertEquals(1, Page::All()->count());

        $this->testData['menu_id'] = $menu2->id;
        $response = $this->post('api/pages?token='.$this->token, $this->testData);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(2, Page::All()->count());
    }

    public function test_it_will_check_uniq_title_by_menu_update_page()
    {
        $menu1 = (app(MenuService::class))->wrapCreate($this->testDataMenu);
        $menu2 = (app(MenuService::class))->wrapCreate(['name' => ['en' => $this->strTestTitle]]);

        $this->assertNotEmpty($menu1->id);
        $this->assertNotEmpty($menu2->id);
        $this->testData['menu_id'] = $menu1->id;
        // page belong to menu
        $p1 = (app(PageService::class))->wrapCreate($this->testData);
        $this->assertNotEmpty($p1->id);

        $title = 'uniq title';
        $this->testData['title']['en'] = $title;
        $p2 = (app(PageService::class))->wrapCreate($this->testData);
        $this->assertNotEmpty($p2->id);
        $this->assertEquals(2, Page::All()->count());

        $response = $this->put('api/pages/'.$p1->id.'?token='.$this->token, $this->testData);
        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
        $this->assertEquals(2, Page::All()->count());
    }

    public function test_it_will_wrong_add_page()
    {
        $testData2 =
        [
            'title' => ['en' => ''],
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);
        $this->assertFalse($response->getData()->success);
        $this->assertNotEmpty($response->getData()->error);
        $this->assertEquals(1, count($response->getData()->error->{'title.en'}));
        $this->assertNotEmpty($response->getData()->error->{'title.en'}[0]);
        $this->assertEquals(1, count($response->getData()->error->{'short_title.en'}));
        $this->assertNotEmpty($response->getData()->error->{'short_title.en'}[0]);
    }

    public function test_it_will_wrong_update_page()
    {
        $this->setTestData();
        $testData2 =
        [
            'title' => ['en' => ''],

        ];

        $id = $this->objPage->id;
        $response = $this->put('api/pages/'.$id.'?token='.$this->token, $testData2);
        $this->assertFalse($response->getData()->success);
        $this->assertNotEmpty($response->getData()->error);
        $this->assertEquals(1, count($response->getData()->error->{'title.en'}));
        $this->assertNotEmpty($response->getData()->error->{'title.en'}[0]);
        $this->assertEquals(1, count($response->getData()->error->{'short_title.en'}));
        $this->assertNotEmpty($response->getData()->error->{'short_title.en'}[0]);
    }

    private function comparePageFields($compareWith, $data)
    {
        $this->assertNotEmpty($data->id);
        $this->assertNotEmpty($data->position);

        $pageFields = Page::FIELDS; // app(PageService::class)->pageFields;
        $this->assertNotEmpty($pageFields);
        foreach ($pageFields as $pageField) {
            if ($pageField != 'id' && $pageField != 'position') {
                $this->assertSame($compareWith[$pageField], $data->{$pageField}, "problem with key= $pageField");
            }
        }

        $this->assertSame($compareWith['title']['en'], $data->title->en);
        $this->assertSame($compareWith['short_title']['en'], $data->short_title->en);
        $this->assertSame($compareWith['description']['en'], $data->description->en);
        $this->assertSame($compareWith['content']['en'], $data->content->en);
    }

    public function test_it_will_show_one_page_see_image_test_page_too()
    {
        $this->setTestData();
        $title2 = 'must be uniq';
        $this->testData['title']['en'] = $title2;
        $objPage2 = (app(PageService::class))->wrapCreate($this->testData);

        $this->assertNotEmpty($this->objPage->id);
        $this->assertNotEmpty($objPage2->id);

        $this->assertNotEquals($this->objPage->id, $objPage2->id);

        $response = $this->get('api/pages/'.$this->objPage->id.'?token='.$this->token);

        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertNotEmpty($res->data);
        $this->assertEquals($this->strTestTitle, $res->data->title->en);
        $this->assertNotEmpty($res->data->id);
        $this->assertEquals($this->objPage->id, $res->data->id);

        $response2 = $this->get('api/pages/'.$objPage2->id.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertNotEmpty($res2->data);
        $this->assertEquals($title2, $res2->data->title->en);
        $this->assertNotEmpty($res2->data->id);
        $this->assertEquals($objPage2->id, $res2->data->id);
    }

    public function test_it_will_show_all_pages()
    {
        $this->setTestData();
        $response = $this->get('api/pages?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 1);

        $data = $res->data[0];
        $this->comparePageFields($this->testData, $data);
    }

    public function test_it_will_not_create_child_for_unpublished_parent()
    {
        $this->setTestData();
        $testData =
        [
            'title' => ['en' => 'test unpublished'],
            'short_title' => ['en' => 'unpublish'],
            'published' => 0,
            'type' => 'cms',
            'content' => ['en' => 'pppppppp'],
            'menu_id' => $this->menuId,
        ];
        $p = (app(PageService::class))->wrapCreate($testData);

        $testDataChild =
        [
            'title' => ['en' => 'test child'],
            'short_title' => ['en' => 'child unpublish parent'],
            'published' => 1,
            'type' => 'cms',
            'content' => ['en' => 'pppppppp2'],
            'page_id' => $p->id,
            'menu_id' => $this->menuId,
        ];
        $pChild = (app(PageService::class))->wrapCreate($testDataChild);

        $this->assertNotEquals($testDataChild['published'], $pChild->published);
        $this->assertEquals(0, $pChild->published);
    }

    public function test_pages_in_header_it_is_in_menu()
    {
        $this->setTestData();

        $short_title_parent = 'test_parent_x123';
        $testData =
        [
            'title' => ['en' => 'test'],
            'short_title' => ['en' => $short_title_parent],
            'published' => 1,
            'type' => 'cms',
            'content' => ['en' => 'pppppppp'],
            'menu_id' => $this->menuId,
        ];
        $p = (app(PageService::class))->wrapCreate($testData);

        $short_title_child1 = 'test_child1_x123';
        $testDataChild1 =
        [
            'title' => ['en' => 'test child1'],
            'short_title' => ['en' => $short_title_child1],
            'published' => 1,
            'type' => 'cms',
            'content' => ['en' => 'pppppppp2'],
            'page_id' => $p->id,
            'menu_id' => $this->menuId,
        ];
        (app(PageService::class))->wrapCreate($testDataChild1);

        $short_title_child2 = 'test_child2_x123';
        $testDataChild2 =
        [
            'title' => ['en' => 'test child2'],
            'short_title' => ['en' => $short_title_child2],
            'published' => 0, // !
            'type' => 'cms',
            'content' => ['en' => 'pppppppp2'],
            'page_id' => $p->id,
            'menu_id' => $this->menuId,
        ];
        (app(PageService::class))->wrapCreate($testDataChild2);

        $short_title_child3 = 'test_child3_x123';
        $testDataChild3 =
        [
            'title' => ['en' => 'test child2'],
            'short_title' => ['en' => $short_title_child3],
            'published' => 1,
            'after_login' => 1, // !
            'type' => 'cms',
            'content' => ['en' => 'pppppppp2'],
            'page_id' => $p->id,
            'menu_id' => $this->menuId,
        ];
        (app(PageService::class))->wrapCreate($testDataChild3);

        $url2 = (app(UrlService::class))->getUrl($p, 'en');
        $response2 = $this->get($url2);
        $response2->assertStatus(200);

        $content = $response2->getContent();
        $this->assertStringContainsString($short_title_parent, $content, "String doesn't contain text=$short_title_parent in menu - header");
        $this->assertStringContainsString($short_title_child1, $content, "String doesn't contain text=$short_title_child1 in menu - header");
        $this->assertStringNotContainsString($short_title_child2, $content, "String does contain text=$short_title_child2 in menu - header");
        $this->assertStringContainsString($short_title_child3, $content, "String does contain text=$short_title_child3 in menu - header"); // TODO we are login as admin (not client)
    }

    public function test_it_will_unpublished_children_by_update()
    {
        $this->setTestData();
        $parentId = $this->dateToTestParent($this->menuId);
        $testData2 =
        [
            'title' => ['en' => PageTest::STR_PARENT_TWO],
            'short_title' => ['en' => 'p22'],
            'published' => 0, // this change
            'type' => 'cms',
            'content' => ['en' => 'parent page ppp2'],
            'menu_id' => $this->menuId,
        ];

        $responseUpdate = $this->put('api/pages/'.$parentId.'?token='.$this->token, $testData2);

        $resUpdate = $responseUpdate->getData();
        $this->assertTrue($resUpdate->success);

        $response2Update = $this->get('api/pages?token='.$this->token);
        $res2Update = $response2Update->getData();
        $this->assertTrue($res2Update->success);

        $pageU = [];
        foreach ($res2Update->data as $pp) {
            if ($pp->page_id == $parentId) {
                $this->assertEquals(0, $pp->published);
                $pageU[] = $pp;
            }
        }

        $this->assertEquals(2, count($pageU));
    }

    public function test_it_will_add_main_page()
    {
        $this->setTestData();
        $parentId = $this->dateToTestParent($this->menuId);
        $testData2 =
        [
            'title' => ['en' => 'test p2xx'],
            'short_title' => ['en' => 'p22'],
            'description' => ['en' => 'test1234'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 1,
            'type' => 'main_page',
            'content' => ['en' => 'aaa test111'],
            'menu_id' => $this->menuId, // it must be null for type main_page
            'page_id' => $parentId, // it must be null for type main_page
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);

        $res = $response->getData();
        $this->assertTrue($res->success);

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $page = [];
        foreach ($res2->data as $p) {
            if ($p->type == 'main_page') {
                $page = $p;
            }
        }
        $this->assertNotEmpty($page);
        $this->assertNotEmpty($page->id);
        $this->assertEquals('main_page', $page->type);
        $this->assertEquals(null, $page->menu_id);
        $this->assertEquals(null, $page->page_id);

        $testData2Update =
        [
            'title' => ['en' => 'Update'],
            'short_title' => ['en' => 'Update'],
            'description' => ['en' => 'test1234'],
            'published' => 1,
            'commented' => 1,
            'after_login' => 1,
            'type' => 'main_page',
            'content' => ['en' => 'aaa test111122'],
            'menu_id' => $this->menuId, // it must be null for type main_page
            'page_id' => $parentId, // it must be null for type main_page
        ];

        $responseUpdate = $this->put('api/pages/'.$page->id.'?token='.$this->token, $testData2Update);

        $resUpdate = $responseUpdate->getData();
        $this->assertTrue($resUpdate->success);

        $response2Update = $this->get('api/pages?token='.$this->token);
        $res2Update = $response2Update->getData();
        $this->assertTrue($res2Update->success);

        $pageU = [];
        foreach ($res2Update->data as $pp) {
            if ($pp->type == 'main_page') {
                $pageU = $pp;
            }
        }

        $this->assertNotEmpty($pageU);
        $this->assertEquals('main_page', $pageU->type);
        $this->assertEquals(null, $pageU->menu_id);
        $this->assertEquals(null, $pageU->page_id);
        $this->assertEquals($testData2Update['title']['en'], $pageU->title->en);
        $this->assertEquals($testData2Update['content']['en'], $pageU->content->en);

        $testData3 =
        [
            'title' => ['en' => 'second main page - wrong!'],
            'short_title' => ['en' => 'p33'],
            'description' => ['en' => 'tes333'],
            'published' => 0,
            'commented' => 0,
            'after_login' => 1,
            'type' => 'main_page',
            'content' => ['en' => 'aaa test3343'],
            'menu_id' => null,
            'page_id' => null,
        ];
        $response3 = $this->post('api/pages?token='.$this->token, $testData3);

        $res3 = $response3->getData();
        $this->assertFalse($res3->success);
    }

    public function test_it_will_add_with_after_login()
    {
        $this->setTestData();
        $testData2 =
        [
            'title' => ['en' => 'test p2'],
            'short_title' => ['en' => 'p22'],
            'description' => ['en' => 'ttt'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 1,
            'type' => 'cms',
            'content' => ['en' => '2test xxx'],
            'page_id' => null,
            'menu_id' => $this->menuId,
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $testData3 =
      [
          'title' => ['en' => 'ppp3'],
          'short_title' => ['en' => 'p33'],
          'description' => ['en' => 'ttt'],
          'published' => 1,
          'commented' => 0,
          'after_login' => 0,
          'type' => 'cms',
          'content' => ['en' => '333test test3435'],
          'page_id' => null,
          'menu_id' => $this->menuId,
      ];

        $response3 = $this->post('api/pages?token='.$this->token, $testData3);
        $res3 = $response3->getData();
        $this->assertTrue($res3->success);

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(count($res2->data), 3);

        $this->assertEquals($res2->data[1]->title->en, $testData2['title']['en']);
        $this->assertEquals($res2->data[1]->after_login, $testData2['after_login']);
        $this->assertEquals($res2->data[1]->content->en, $testData2['content']['en']);

        $this->assertEquals($res2->data[2]->title->en, $testData3['title']['en']);
        $this->assertEquals($res2->data[2]->after_login, $testData3['after_login']);
        $this->assertEquals($res2->data[2]->content->en, $testData3['content']['en']);

        $menu = Menu::findOrFail($this->menuId);
        $pagePublish = (app(MenuService::class))->pagesPublishedAndAccess($menu, Auth::check())->toArray();

        $this->assertEquals(2, count($pagePublish));
    }

    public function test_it_will_check_type_pages()
    {
        $this->setTestData();
        $this->assertNotEmpty($this->menuId);
        $parentId = $this->dateToTestParent($this->menuId);
        $this->assertNotEmpty($parentId);

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $testItem = $res2->data[3];

        $this->assertIsInt($testItem->id);
        $this->assertIsInt($testItem->published);
        $this->assertIsInt($testItem->commented);
        $this->assertIsInt($testItem->after_login);
        $this->assertIsInt($testItem->position);
        $this->assertIsInt($testItem->menu_id);
        $this->assertEquals($this->menuId, $testItem->menu_id);
        $this->assertIsInt($testItem->page_id);
        $this->assertEquals($parentId, $testItem->page_id);
    }

    public function test_it_will_delete_parent()
    {
        $this->setTestData();
        $parentId = $this->dateToTestParent($this->menuId);
        $pages = Page::All()->toArray();

        $pagesBeforeCount = count($pages);
        $this->assertEquals($pagesBeforeCount, 6);

        $pagesChild = Page::query()->where('page_id', $parentId)->orderBy('position', 'asc')->get()->toArray();
        $this->assertEquals(count($pagesChild), 2);

        $this->assertEquals($pagesChild[0]['page_id'], $parentId);
        $this->assertEquals($pagesChild[1]['page_id'], $parentId);

        $pageToDel = Page::findOrFail($parentId);
        $this->assertNotEmpty($pageToDel->id);
        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang($pageToDel, 'title', 'en'), PageTest::STR_PARENT_TWO);
        $pageToDel->delete();

        $pagesAfter = Page::All()->toArray();
        $this->assertEquals(count($pagesAfter), $pagesBeforeCount - 1);

        $pagesChildAfter = Page::query()->where('page_id', $parentId)->orderBy('position', 'asc')->get()->toArray();
        $this->assertEquals(count($pagesChildAfter), 0);

        $pageAfter1 = Page::findOrFail($pagesChild[0]['id']);
        $pageAfter2 = Page::findOrFail($pagesChild[1]['id']);

        $this->assertEquals($pageAfter1->page_id, null);
        $this->assertEquals($pageAfter2->page_id, null);
    }

    public function test_it_will_add_test_page_id_check_position_child()
    {
        $this->setTestData();
        $parentId = $this->dateToTestParent($this->menuId);

        $pages = Page::query()->where('page_id', $parentId)->orderBy('position', 'asc')->get()->toArray();

        $this->assertEquals(count($pages), 2);

        $this->assertEquals($pages[0]['page_id'], $parentId);
        $this->assertEquals($pages[1]['page_id'], $parentId);

        $positionBefore1 = $pages[0]['position'];
        $positionBefore2 = $pages[1]['position'];

        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages[0]['id']), 'title', 'en'), PageTest::STR_CHILD_ONE);
        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages[1]['id']), 'title', 'en'), PageTest::STR_CHILD_TWO);

        $res2a = $this->patch('api/pages/position/up/'.$pages[0]['id'].'?token='.$this->token);

        $res22a = $res2a->getData();
        $this->assertTrue($res22a->success);

        $pages22 = Page::query()->where('page_id', $parentId)->orderBy('position', 'asc')->get()->toArray();

        $positionAfter1 = $pages22[0]['position'];
        $positionAfter2 = $pages22[1]['position'];

        $this->assertNotEmpty($positionAfter1);
        $this->assertNotEmpty($positionAfter2);

        $this->assertEquals($positionBefore1, $positionAfter1);
        $this->assertEquals($positionBefore2, $positionAfter2);

        $this->assertEquals(PageTest::STR_CHILD_TWO, (app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages22[0]['id']), 'title', 'en'));
        $this->assertEquals(PageTest::STR_CHILD_ONE, (app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages22[1]['id']), 'title', 'en'));
    }

    public function test_it_will_add_test_page_id_check_position_parent()
    {
        $this->setTestData();
        $parentId = $this->dateToTestParent($this->menuId);

        $pages = Page::query()->where('page_id', null)->where('menu_id', $this->menuId)->orderBy('position', 'asc')->get()->toArray();

        $this->assertEquals(count($pages), 3);

        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages[1]['id']), 'title', 'en'), PageTest::STR_PARENT_TWO);
        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages[2]['id']), 'title', 'en'), PageTest::STR_PARENT_TREE);

        $this->assertEquals($pages[1]['page_id'], null);
        $this->assertEquals($pages[2]['page_id'], null);

        $positionBefore1 = $pages[1]['position'];
        $positionBefore2 = $pages[2]['position'];
        $this->assertNotEmpty($positionBefore1);
        $this->assertNotEmpty($positionBefore2);
        $this->assertTrue($positionBefore1 < $positionBefore2);

        $res2a = $this->patch('api/pages/position/down/'.$pages[1]['id'].'?token='.$this->token);

        $res22a = $res2a->getData();
        $this->assertTrue($res22a->success);

        $pages22 = Page::query()->where('page_id', null)->where('menu_id', $this->menuId)->orderBy('position', 'asc')->get()->toArray();

        $positionAfter1 = $pages22[1]['position'];
        $positionAfter2 = $pages22[2]['position'];

        $this->assertNotEmpty($positionAfter1);
        $this->assertNotEmpty($positionAfter2);

        $this->assertEquals(PageTest::STR_PARENT_TREE, (app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages22[1]['id']), 'title', 'en'));
        $this->assertEquals(PageTest::STR_PARENT_TWO, (app(TranslationReader::class))->translatesByColumnAndLang(Page::find($pages22[2]['id']), 'title', 'en'));
    }

    public function test_it_will_add3a_with_menu_pages()
    {
        $this->setTestData();
        $testData3 =
        [
            'title' => ['en' => 'test p3'],
            'short_title' => ['en' => 'p33'],
            'published' => 0,
            'type' => 'cms',
            'content' => ['en' => 'lerem ipsum ..'],
            'menu_id' => $this->menuId,
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData3);
        $this->assertTrue($response->getData()->success);

        $this->assertEquals(1, $this->menuObj->pages->count());

        $pagesPublished = (app(MenuService::class))->pagesPublished($this->menuObj);
        $this->assertEquals(0, $pagesPublished->count());
    }

    public function test_it_will_add3_with_menu_pages()
    {
        $this->setTestData();
        $testData3 =
        [
            'title' => ['en' => 'test p3'],
            'short_title' => ['en' => 'p33'],
            'published' => 0,
            'type' => 'cms',
            'content' => ['en' => 'sdafsfsdaf asdfasdf'],
            'menu_id' => $this->menuId,
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData3);
        $this->assertTrue($response->getData()->success);

        $testData2 =
      [
          'title' => ['en' => 'test p2222'],
          'short_title' => ['en' => 'p22222'],
          'published' => 1,
          'type' => 'cms',
          'content' => ['en' => 'sdafsfsdaf asdfasdf'],
          'menu_id' => $this->menuId,
      ];

        $response2 = $this->post('api/pages?token='.$this->token, $testData2);
        $this->assertTrue($response2->getData()->success);

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(count($res2->data), 3);

        $this->assertEquals(2, count($this->menuObj->pages));

        $pagesPublished = (app(MenuService::class))->pagesPublished($this->menuObj);
        $this->assertEquals(1, $pagesPublished->count());  // only one has got published ===1 for 'menu_id' =>  $this->menuId
        $this->assertNotEmpty($pagesPublished->first()->id);
        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang($pagesPublished->first(), 'title', 'en'), $testData2['title']['en']);
    }

    public function test_it_will_add_pages_to_check_position_docs()
    {
        $this->setTestData();
        $testData2 =
      [
          'title' => ['en' => 'test p2'],
          'short_title' => ['en' => 'p22'],
          'published' => 0,
          'type' => 'cms',
          // 'content' => null
          'menu_id' => null,
      ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $pageId = $res->data->pageId;
        $this->assertTrue(! empty($pageId));

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $this->assertEquals(count($res2->data), 2);
        $data = (array) $res2->data;
        $this->assertEquals($res2->data[1]->position, 2);

        // menu_id is defined
        $testDataWithMenu =
      [
          'title' => ['en' => 'test p2'],
          'short_title' => ['en' => 'p22'],
          'published' => 0,
          'type' => 'cms',
          'menu_id' => $this->menuId,
      ];

        $testDataWithMenuB =
      [
          'title' => ['en' => 'BB'],
          'short_title' => ['en' => 'BB p22'],
          'published' => 0,
          'type' => 'cms',
          'menu_id' => $this->menuId,
      ];

        $response3 = $this->post('api/pages?token='.$this->token, $testDataWithMenu);
        $res3 = $response3->getData();
        $this->assertTrue($res3->success);
        $response3b = $this->post('api/pages?token='.$this->token, $testDataWithMenuB);
        $res3b = $response3b->getData();
        $this->assertTrue($res3b->success);

        $response22 = $this->get('api/pages?token='.$this->token);
        $res22 = $response22->getData();
        $this->assertTrue($res22->success);

        $this->assertEquals(count($res22->data), 4);
        $data22 = (array) $res22->data;

        $tmpArr = [];
        foreach ($data22 as $key => $page) {
            if ($page->menu_id == $this->menuId) {
                $tmpArr[] = $page;
            }
        }

        $this->assertEquals(count($tmpArr), 2);

        $this->assertEquals($tmpArr[0]->title->en, $testDataWithMenu['title']['en']);
        $this->assertEquals($tmpArr[1]->title->en, $testDataWithMenuB['title']['en']);

        $positionBefore = $tmpArr[1]->position;
        $this->assertNotEmpty($positionBefore);
        $this->assertEquals($positionBefore, 2);

        // 2x change position - and result should be the same.
        $res1a = $this->patch('api/pages/position/down/'.$tmpArr[1]->id.'?token='.$this->token);

        $res22a = $res1a->getData();
        $this->assertTrue($res22a->success);

        $res22firstData = Page::all();

        // not work  ??
        // $response22 = $this->get('api/pages?token='.$this->token );
        // $res22first = $response22->getData();
        // $this->assertTrue($res22first->success);

        // //print_r($tmpArr[1]->id);
        // print_r($res22first->data); //po!!!
        // exit;

        // chose one record
        $item = [];
        foreach ($res22firstData as $page) {
            if ($page->id == $tmpArr[1]->id) {
                $item = $page;
            }
        }

        $this->assertNotEmpty($item->position);
        $this->assertNotEquals($item->position, $positionBefore);
        $this->assertEquals($item->position, 1);

        $res2b = $this->patch('api/pages/position/down/'.$tmpArr[1]->id.'?token='.$this->token);
        $res22b = $res2b->getData();
        $this->assertTrue($res22b->success);

        $data22c = Page::all();
        $tmpArr2 = [];
        foreach ($data22c as $key => $pageC) {
            if ($pageC->menu_id == $this->menuId) {
                $tmpArr2[] = $pageC;
            }
        }

        $this->assertSame($tmpArr[0]->title->en, (app(TranslationReader::class))->translatesByColumnAndLang(Page::find($tmpArr2[0]->id), 'title', 'en'));
    }

    public function test_it_will_add_pages0()
    {
        $this->setTestData();
        $testData2 =
      [
          'title' => ['en' => 'test p2'],
          'short_title' => ['en' => 'p22'],
          'description' => ['en' => 'test1234'],
          'published' => 0,
          'commented' => 0,
          'after_login' => 0,
          'type' => 'contact',
          'content' => ['en' => 'aaa ffdfds'],
          'menu_id' => null,
          'page_id' => null,
      ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);
        $res = $response->getData();
        $this->assertTrue($res->success);

        // check records in db
        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $this->assertEquals(count($res2->data), 2);
        $data = $res2->data[0];

        $this->comparePageFields($this->testData, $data);

        $data2 = $res2->data[1];
        $this->comparePageFields($testData2, $data2);

        // min data
        $testData22 =
        [
            'title' => ['en' => 'test p2 uniq'],
            'short_title' => ['en' => 'p2'],
            'position' => '3a12',
        ];

        $response22 = $this->post('api/pages?token='.$this->token, $testData22);
        $res22 = $response22->getData();
        $this->assertTrue($res22->success);

        $pages = Page::all()->toArray();
        $lastPage = $pages[count($pages) - 1];
        $this->assertEquals('cms', $lastPage['type']); // cms - it is default value
    }

    public function test_it_will_get_pages_by_type_docs()
    {
        $testData2 =
            [
                'title' => ['en' => 'test p2'],
                'short_title' => ['en' => 'p22'],
                'published' => 0,
                'type' => 'shop',
                'content' => ['en' => 'aaa ffdfds'],
                'menu_id' => null,
                // 'images' => []
            ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $type = 'shop';
        $res = $this->get('api/pages/type/'.$type.'?token='.$this->token);
        // dd($res->getContent());

        $data = $res->getData();
        $this->assertTrue($data->success);

        $this->assertEquals(1, count($data->data));
        $this->assertEquals($type, $data->data[0]->type);

        $typeErr = 'fake123';
        $res = $this->get('api/pages/type/'.$typeErr.'?token='.$this->token);
        $data = $res->getData();
        $this->assertTrue($data->success);
        $this->assertEmpty(count($data->data));
    }

    public function test_it_will_add2_with_menu_pages()
    {
        $this->setTestData();
        $testData2 =
      [
          'title' => ['en' => 'test p2'],
          'short_title' => ['en' => 'p22'],
          'description' => ['en' => 'ttt'],
          'published' => 0,
          'commented' => 0,
          'after_login' => 0,
          'type' => 'cms',
          'content' => ['en' => 'sdafsfsdaf asdfasdf'],
          'page_id' => null,
          'menu_id' => 2354, // $this->menuId
      ];

        $responseFake = $this->post('api/pages?token='.$this->token, $testData2);

        $resFake = $responseFake->getData();
        $this->assertFalse($resFake->success);
        $this->assertNotEmpty($resFake->error);

        $testData2['menu_id'] = $this->menuId;

        $response = $this->post('api/pages?token='.$this->token, $testData2);

        $res = $response->getData();
        $this->assertTrue($res->success);

        // check records in db
        $response2 = $this->get('api/pages?token='.$this->token);

        $res2 = $response2->getData();

        $this->assertTrue($res2->success);
        $this->assertEquals(count($res2->data), 2);

        $this->comparePageFields($this->testData, $res2->data[0]);
        $this->comparePageFields($testData2, $res2->data[1]);
    }

    public function test_it_will_get_slug()
    {
        $this->setTestData();
        $responseAll = $this->get('api/pages?token='.$this->token);
        $resAll = $responseAll->getData();
        $id = $resAll->data[0]->id;

        $this->assertNotEmpty($id);

        $slug = (app(UrlService::class))->getSlugByLang(Page::find($id), 'en');
        $this->assertEquals($slug, Str::slug($this->testData['title']['en'], '-'));
    }

    public function test_it_will_update_page()
    {
        $this->setTestData();
        $responseAll = $this->get('api/pages?token='.$this->token);
        $resAll = $responseAll->getData();
        $id = $resAll->data[0]->id;

        $this->assertNotEmpty($id);

        $slug = (app(UrlService::class))->getSlugByLang(Page::find($id), 'en');
        $this->assertEquals($slug, Str::slug($this->testData['title']['en'], '-'));

        $testData3 =
      [
          'id' => $id,
          'title' => ['en' => 'test p3 żółta żółć'],
          'short_title' => ['en' => 'p3'],
          'description' => ['en' => 'sss'],
          'published' => 1,
          'commented' => 0,
          'after_login' => 0,
          'type' => 'cms',
          'content' => ['en' => 'gg'],
          'menu_id' => null,
          'page_id' => null,
          'images' => [],
      ];

        $response0 = $this->put('api/pages/'.$id.'?token='.$this->token, $testData3);

        $slugAfter = (app(UrlService::class))->getSlugByLang(Page::find($id), 'en');
        $this->assertNotEquals($slug, $slugAfter);
        $this->assertEquals($slugAfter, Str::slug($testData3['title']['en'], '-'));

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        // retrieve $menus
        $response = $this->get('api/pages?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 1);
        $data = $res->data[0];

        $this->comparePageFields($testData3, $data);

        // wrong data
        $testData33 =
      [
          'id' => $id,
          'title' => ['en' => 'test p3'],
          'short_title' => ['en' => 'p3'],
          'commented' => 1,
          'position' => '3d33',
      ];
        $response33 = $this->put('api/pages/'.$id.'?token='.$this->token, $testData33);
        $res33 = $response33->getData();
        $this->assertTrue($res33->success);
    }

    public function test_it_will_update2_page_with_menu()
    {
        // $this->markTestSkipped('dont use getAllTranslate, therefore we skip this test' );
        $this->setTestData();
        $responseAll = $this->get('api/pages?token='.$this->token);
        $resAll = $responseAll->getData();
        $id = $resAll->data[0]->id;

        $this->assertNotEmpty($id);

        $page = Page::All();
        $this->assertEquals($page->count(), 1);

        $pageObj = $page->first();
        $this->assertEquals($this->testData['title']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'title', 'en'));
        $this->assertEquals($this->testData['short_title']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'short_title', 'en'));
        $this->assertEquals($this->testData['description']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'description', 'en'));
        $this->assertEquals($this->testData['content']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'content', 'en'));

        // 'title' => ['en' => $this->strTestTitle],
        // 'short_title' => ['en' => `'page1'`],
        // 'description' => ['en' => 'this page: test desc ...'],
        // 'published' => 1,
        // 'commented' => 1,
        // 'after_login' => 0,
        // 'type' => 'cms',
        // 'content' => ['en' => 'content test133445'],

        // $allTranslate = (app(TranslationReader::class))->getAllTranslate(Page::find($id)); //dont use
        // $this->assertEquals(4, count($allTranslate));

        // $id = 1;
        $testData3 =
        [
            'id' => $id,
            'title' => ['en' => 'test p3333'],
            'short_title' => ['en' => 'p3333'],
            'description' => ['en' => null],
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'content' => ['en' => null],
            'type' => 'cms',
            'page_id' => null,
            'menu_id' => 9123,
            'images' => [],
        ];

        $responseFake = $this->put('api/pages/'.$id.'?token='.$this->token, $testData3);

        $resFake = $responseFake->getData();
        $this->assertFalse($resFake->success);
        // dd($resFake->error);
        $this->assertNotEmpty($resFake->error);

        $testData3['menu_id'] = $this->menuId;
        $response0 = $this->put('api/pages/'.$id.'?token='.$this->token, $testData3);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        // retrieve $menus
        $response = $this->get('api/pages?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 1);
        $data = $res->data[0];

        $page2 = Page::All();
        $this->assertEquals($page2->count(), 1);
        $pageObj2 = $page2->first();

        $this->assertEquals($testData3['title']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj2, 'title', 'en'));
        $this->assertEquals($testData3['short_title']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj2, 'short_title', 'en'));
        $this->assertEquals($testData3['description']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj2, 'description', 'en'));
        $content = app(TranslationReader::class)->translatesByColumnAndLang($pageObj2, 'content', 'en');
        $this->assertEquals($testData3['content']['en'], $content);

        // $allTranslate = (app(TranslationReader::class))->getAllTranslate(Page::find($id)); //dont use
        // $this->assertEquals(4, count($allTranslate));

        $this->comparePageFields($testData3, $data);
    }

    public function test_it_will_update_empty_val()
    {
        // $this->markTestSkipped('dont use getAllTranslate, therefore we skip this test' );
        $menu = (app(MenuService::class))->wrapCreate($this->testDataMenu);
        $this->assertNotEmpty($menu->id);

        $testData =
        [
            'title' => ['en' => 'test p3333'],
            'short_title' => ['en' => 'p3333'],
            'description' => ['en' => null],
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'content' => ['en' => null],
            'type' => 'cms',
            'page_id' => null,
            'menu_id' => $menu->id,
            'images' => [],
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData);
        $this->assertTrue($response->getData()->success);

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(count($res2->data), 1);

        $data = $res2->data[0];
        $pageId = $data->id;
        $this->assertNotEmpty($pageId);
        // $allTranslate = (app(TranslationReader::class))->getAllTranslate(Page::find($pageId)); //dont use
        // $this->assertEquals(4, count($allTranslate));

        $page = Page::All();
        $this->assertEquals($page->count(), 1);
        $pageObj = $page->first();
        $this->assertEquals($testData['title']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'title', 'en'));
        $this->assertEquals($testData['short_title']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'short_title', 'en'));
        $this->assertEquals($testData['description']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'description', 'en'));
        $this->assertEquals($testData['content']['en'], app(TranslationReader::class)->translatesByColumnAndLang($pageObj, 'content', 'en'));

        $this->comparePageFields($testData, $data);
    }

    public function test_it_will_delete_page()
    {
        $this->setTestData();
        $responseAll = $this->get('api/pages?token='.$this->token);
        $resAll = $responseAll->getData();
        $this->assertNotEmpty($resAll->data);
        $id = $resAll->data[0]->id;
        $this->assertNotEmpty($id);

        $response0 = $this->delete('api/pages/'.$id.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $responseAllAfter = $this->get('api/pages?token='.$this->token);
        $resAllAfter = $responseAllAfter->getData();
        $this->assertEmpty($resAllAfter->data);
    }

    public function test_it_will_delete_page_fake()
    {

        $responseFake = $this->delete('api/pages/rs_I_eW23423fsd?token='.$this->token);
        $responseFake->assertStatus(404);
        $resFake = $responseFake->getData();
        $this->assertFalse($resFake->success);
        $this->assertNotEmpty($resFake->error);
        $this->assertNotEmpty($resFake->message);

        $data = $responseFake->json();

        $this->assertArrayHasKey('message', $data);
        $this->assertNotEmpty($data['message']);
        // dd($data);

        // fake id - error handlings
        // $responseFake = $this->delete('api/pages/rs_I_eW23423fsd?token='.$this->token);
        // dd($responseFake->getContent());
        // $resFake = $responseFake->getData();
        // $this->assertFalse($resFake->success);
        // $this->assertNotEmpty($resFake->error);
    }

    public function test_it_will_get_pages_by_short_title_default_lang()
    {
        $shortTitleTest = 'p22_test';
        $testData2 =
            [
                'title' => ['en' => 'test p2'],
                'short_title' => ['en' => $shortTitleTest],
                'published' => 1,
                'type' => 'shop',
                'content' => ['en' => 'some test content'],
                'menu_id' => null,
                // 'images' => []
            ];

        $response = $this->post('api/pages?token='.$this->token, $testData2);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $pages = (app(PageService::class))->getAllPagesWithImagesByShortTitleForDefaultLang($shortTitleTest);

        $this->assertEquals(1, count($pages));
        $this->assertEquals($shortTitleTest, $pages[0]['short_title']['en']);
    }

    /** start - headless - forbidden */
    public function test_it_will_get_all_pages_by_type_without_auth_forbidden()
    {
        $type = 'inner';
        $predefinedShortTitle = ['main_page_box1', 'main_page_box2', 'main_page_box3', 'company_data', 'main_page_slider'];
        $this->prepareTestDataForGetByType($type, $predefinedShortTitle);

        $response = $this->get('api/pages-type/'.$type);
        $this->assertEquals(404, $response->status());
    }

    public function test_it_will_get_first_page_by_short_title_without_auth_forbidden()
    {
        $type = 'inner';
        $shortTitle = 'main_page_box';
        $predefinedShortTitle = [$shortTitle.'1', $shortTitle.'2', $shortTitle.'3', 'company_data', 'main_page_slider'];
        $this->prepareTestDataForGetByType($type, $predefinedShortTitle);

        $res = $this->get('api/pages-short-title/'.$shortTitle);
        $this->assertEquals(404, $res->status());
    }

    public function test_it_will_get_one_page_without_auth_forbidden()
    {
        $testData =
        [
            'title' => ['en' => 'inner title'],
            'short_title' => ['en' => 'inner short_title'],
            'type' => 'inner',
            'content' => ['en' => 'content test4333 inner'],
        ];

        $objPage = (app(PageService::class))->wrapCreate($testData);
        $this->assertNotEmpty($objPage->id);
        $res = $this->get('api/page/'.$objPage->id);
        $this->assertEquals(404, $res->status());
    }
    /** stop - headless - forbidden */
}
