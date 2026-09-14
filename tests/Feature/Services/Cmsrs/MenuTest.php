<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Menu;
use App\Models\Cmsrs\Page;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\Translation\TranslationReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MenuTest extends Base
{
    use RefreshDatabase;

    private $testData;

    private $testPageData;    

    private $objMenu;

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

        $this->testData =
        [
            'name' => ['en' => 'test menu1'],
        ];

        $this->testPageData =
        [
            'title' => ['en' => 'some title'],
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

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function setTestData()
    {
        $this->objMenu = (app(MenuService::class))->wrapCreate($this->testData);
    }

    public function test_it_will_count_item_in_menu_empty()
    {
        $this->assertTrue((app(MenuService::class))->isWelcomePage());        
    }    

    public function test_it_will_count_item_in_menu_one()
    {
        $this->setTestData();
        $this->assertFalse((app(MenuService::class))->isWelcomePage());        
    } 

    public function test_it_will_one_page()
    {
        (app(PageService::class))->wrapCreate($this->testPageData);
        $this->assertFalse((app(MenuService::class))->isWelcomePage());
    }    

    public function test_it_will_check_uniq_name_add_menus()
    {
        $nameEn = 'test menu1';
        $testData1 =
        [
            'name' => ['en' => $nameEn],
        ];

        $objMenu1 = (app(MenuService::class))->wrapCreate($testData1);
        $this->assertNotEmpty($objMenu1->id);

        $ttt = 'ttt';
        $testData =
        [
            'title' => ['en' => $nameEn],
            'short_title' => ['en' => $ttt],
            'published' => 0,
            'type' => 'cms',
            'content' => ['en' => 'pppppppp'],
            'menu_id' => $objMenu1->id,
        ];

        $response = $this->post('api/pages?token='.$this->token, $testData);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $response2ttt = $this->post('api/menus?token='.$this->token, ['name' => ['en' => $ttt]]);
        $res1 = $response2ttt->getData();
        $this->assertTrue($res1->success);

        $response2 = $this->post('api/menus?token='.$this->token, $testData1);

        $res2 = $response2->getData();
        $this->assertFalse($res2->success);
        $this->assertNotEmpty($res2->error);
    }

    public function test_it_will_check_uniq_name_update_menus()
    {
        $nameEn1 = 'test menu1';
        $testData1 =
      [
          'name' => ['en' => $nameEn1],
      ];
        $objMenu1 = (app(MenuService::class))->wrapCreate($testData1);
        $this->assertNotEmpty($objMenu1->id);

        $nameEn2 = 'test menu2';
        $testData2 =
      [
          'name' => ['en' => $nameEn2],
      ];
        $objMenu2 = (app(MenuService::class))->wrapCreate($testData2);
        $this->assertNotEmpty($objMenu2->id);

        $response = $this->put('api/menus/'.$objMenu2->id.'?token='.$this->token, $testData1);
        $res = $response->getData();
        $this->assertFalse($res->success);
    }

    public function test_it_will_wrong_add()
    {
        $testData2 =
      [
          'name' => ['en' => ''],
      ];

        $response = $this->post('api/menus?token='.$this->token, $testData2);
        $this->assertFalse($response->getData()->success);
        $this->assertNotEmpty($response->getData()->error);
        $this->assertEquals(1, count($response->getData()->error->{'name.en'}));
        $this->assertNotEmpty($response->getData()->error->{'name.en'}[0]);
    }

    public function test_it_will_wrong_update()
    {
        $this->setTestData();
        $testData2 =
      [
          'name' => ['en' => ''],
      ];

        $id = $this->objMenu->id;
        $response = $this->put('api/menus/'.$id.'?token='.$this->token, $testData2);
        $this->assertFalse($response->getData()->success);
        $this->assertNotEmpty($response->getData()->error);
        $this->assertEquals(1, count($response->getData()->error->{'name.en'}));
        $this->assertNotEmpty($response->getData()->error->{'name.en'}[0]);
    }

    public function test_it_will_get_slug()
    {
        $this->setTestData();
        $responseAll = $this->get('api/menus?token='.$this->token);
        $resAll = $responseAll->getData();
        $id = $resAll->data[0]->id;

        $slug = (app(MenuService::class))->getSlugByLang(Menu::find($id), 'en');
        $this->assertEquals($slug, Str::slug($this->testData['name']['en'], '-'));
    }

    public function test_it_will_get_tree_by_menu()
    {
        $this->setTestData();
        $parentId = $this->dateToTestParent($this->objMenu->id);

        $publishedAndAccess = (app(MenuService::class))->pagesPublishedAndAccess($this->objMenu, Auth::check()); // ->toArray();
        $tree = (app(MenuService::class))->pagesPublishedTree($publishedAndAccess);
        $this->assertEquals(3, count($tree));
        $this->assertEquals(2, count($tree[$parentId]['children']));

        $page0 = Page::find($tree[$parentId]['children'][0]->id);
        $this->assertEquals($tree[$parentId]['children'][0]->id, $page0->id);

        $page1 = Page::find($tree[$parentId]['children'][1]->id);
        $this->assertEquals($tree[$parentId]['children'][1]->id, $page1->id);

        $title0 = (app(TranslationReader::class))->translatesByColumnAndLang($page0, 'title', 'en');
        $title1 = (app(TranslationReader::class))->translatesByColumnAndLang($page1, 'title', 'en');

        $this->assertEquals(PageTest::STR_CHILD_ONE, $title0);
        $this->assertEquals(PageTest::STR_CHILD_TWO, $title1);
    }

    public function test_it_will_change_position_menus_docs()
    {
        $this->setTestData();

        // add to test menu
        $testData2 =
      [
          'name' => ['en' => 'test menu2'],
      ];
        $response2 = $this->post('api/menus?token='.$this->token, $testData2);
        $this->assertTrue($response2->getData()->success);

        $testData3 =
      [
          'name' => ['en' => 'test menu3'],
      ];
        $response3 = $this->post('api/menus?token='.$this->token, $testData3);
        $this->assertTrue($response3->getData()->success);
        // test if 3 $menus
        $response = $this->get('api/menus?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 3);

        $this->assertEquals($res->data[0]->position, 1);
        $this->assertEquals($res->data[1]->position, 2);
        $this->assertEquals($res->data[2]->position, 3);

        $name = (app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($res->data[2]->id), 'name', 'en');
        $this->assertNotEmpty($name);
        $this->assertEquals($name, $testData3['name']['en']);

        $res = $this->patch('api/menus/position/up/'.$res->data[2]->id.'?token='.$this->token);
        $res22a = $res->getData();
        $this->assertTrue($res22a->success);

        $response = $this->get('api/menus?token='.$this->token);
        $res1 = $response->getData();

        $name2 = (app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($res1->data[2]->id), 'name', 'en');
        $this->assertEquals($name2, $testData2['name']['en']);
        $this->assertNotEquals($name2, $name);

        $name1 = (app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($res1->data[2]->id), 'name', 'en');
        $resDown = $this->patch('api/menus/position/down/'.$res1->data[2]->id.'?token='.$this->token);
        $res22a1 = $resDown->getData();
        $this->assertTrue($res22a1->success);

        $response = $this->get('api/menus?token='.$this->token);
        $res2 = $response->getData();

        $this->assertNotEquals((app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($res2->data[2]->id), 'name', 'en'), $name1);

        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($res2->data[0]->id), 'name', 'en'), $name1);
        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($res2->data[0]->id), 'name', 'en'), 'test menu2');
        $this->assertEquals($res2->data[0]->position, 1);
    }

    public function test_it_will_show_all_menus()
    {
        $this->setTestData();

        $response = $this->get('api/menus?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 1);

        $data = (array) $res->data[0];

        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($data['id']), 'name', 'en'), $data['name']->en);
        $this->assertSame((app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($data['id']), 'name', 'en'), $this->testData['name']['en']);
        $this->assertSame($data['position'], 1); // instead 1 use $this->testData['position']

        $this->assertIsInt($data['position']);
        $this->assertIsInt($data['id']);

        $this->assertNotEmpty($data['id']);
    }

    public function test_it_will_add_menus_docs()
    {
        $this->setTestData();
        $testData2 =
      [
          'name' => ['en' => 'test menu2'],
      ];

        $response = $this->post('api/menus?token='.$this->token, $testData2);

        $res = $response->getData();
        $this->assertTrue($res->success);

        // check rows in db
        $response2 = $this->get('api/menus?token='.$this->token);

        $res2 = $response2->getData();

        $this->assertTrue($res2->success);
        $this->assertEquals(count($res2->data), 2);

        $data = (array) $res2->data[0];
        $this->assertSame((app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($data['id']), 'name', 'en'), $data['name']->en);
        $this->assertSame((app(TranslationReader::class))->translatesByColumnAndLang(Menu::find($data['id']), 'name', 'en'), $this->testData['name']['en']);
        $data2 = (array) $res2->data[1];

        $testData2['position'] = $data['position'] + 1;

        $this->assertSame($data2['position'], $testData2['position']);

        // wrong data
        $testData22 =
      [
          'name' => ['en' => 'test menu22'],
          'position' => '3a12',
          'fake' => 234,
      ];

        $response22 = $this->post('api/menus?token='.$this->token, $testData22);

        $res22 = $response22->getData();
        $this->assertTrue($res22->success);

        // wrong lang
        $testData44 =
      [
          'name_fake' => ['en' => 'test menu2222'],
          'position' => '3a12',
          'fake' => 234,
      ];

        $response44 = $this->post('api/menus?token='.$this->token, $testData44);
        $this->assertFalse($response44->getData()->success);
        $this->assertNotEmpty($response44->getData()->error);

        // wrong lang
        $testData55 =
      [
          'name' => ['fr' => 'test menu2'],
          'position' => '3a12',
          'fake' => 234,
      ];

        $response55 = $this->post('api/menus?token='.$this->token, $testData55);
        $this->assertFalse($response55->getData()->success);
        $this->assertNotEmpty($response55->getData()->error);
    }

    public function test_it_will_update_menu_uniq_docs()
    {
        $this->setTestData();
        $responseAll = $this->get('api/menus?token='.$this->token);
        $resAll = $responseAll->getData();
        $id = $resAll->data[0]->id;

        $slug = (app(MenuService::class))->getSlugByLang(Menu::find($id), 'en');
        $this->assertEquals($slug, Str::slug($this->testData['name']['en'], '-'));

        $testData3 =
      [
          'id' => $id,
          'name' => ['en' => 'test menu3  żółta żółć'],
      ];

        $response0 = $this->put('api/menus/'.$id.'?token='.$this->token, $testData3);
        $this->assertTrue($response0->getData()->success);

        $slugAfter = (app(MenuService::class))->getSlugByLang(Menu::find($id), 'en');
        $this->assertNotEquals($slug, $slugAfter);
        $this->assertEquals($slugAfter, Str::slug($testData3['name']['en'], '-'));

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        // retrieve $menus
        $response = $this->get('api/menus?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 1);
        $data = (array) $res->data[0];

        $this->assertSame($data['id'], $testData3['id']);
        $this->assertSame($data['name']->en, $testData3['name']['en']);
        $this->assertNotEmpty($data['position']);
        $this->assertFalse(isset($testData3['position']));

        // wrong data
        $testData33 =
      [
          'id' => $id,
          'name' => ['fr' => 'test menu3'],
          'position' => '3d33',
      ];
        $response33 = $this->put('api/menus/'.$id.'?token='.$this->token, $testData33);
        $res33 = $response33->getData();
        $this->assertFalse($res33->success);
    }

    public function test_it_will_delete_menu_docs()
    {
        $this->setTestData();
        $responseAll = $this->get('api/menus?token='.$this->token);
        $resAll = $responseAll->getData();
        $this->assertNotEmpty($resAll->data);
        $id = $resAll->data[0]->id;
        $this->assertNotEmpty($id);

        $response0 = $this->delete('api/menus/'.$id.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $responseAllAfter = $this->get('api/menus?token='.$this->token);
        $resAllAfter = $responseAllAfter->getData();
        $this->assertEmpty($resAllAfter->data);
    }

    public function test_it_will_delete_menu_fake()
    {
        $this->setTestData();
        // fake id - error handling
        $responseFake = $this->delete('api/menus/rs_I_eW23423fsd?token='.$this->token);
        // dd($responseFake->getContent());
        $resFake = $responseFake->getData();
        $this->assertFalse($resFake->success);
        $this->assertNotEmpty($resFake->error);
        $this->assertNotEmpty($resFake->message);
    }

    public function test_it_will_delete_fake_menu()
    {
        $id = 99999;
        $response0 = $this->delete('api/menus/'.$id.'?token='.$this->token);
        $response0->assertStatus(404);
        $resFake = $response0->getData();

        $this->assertFalse($resFake->success);
        $this->assertNotEmpty($resFake->error);
        $this->assertNotEmpty($resFake->message);
    }
}
