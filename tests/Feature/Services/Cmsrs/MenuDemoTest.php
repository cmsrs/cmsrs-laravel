<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Menu;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Translation\TranslationReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class MenuDemoTest extends Base
{
    use RefreshDatabase;

    private $testData;

    private $objMenu;

    protected function setUp(): void
    {
        putenv('LANGS="en"');
        putenv('API_SECRET=""');
        putenv('CURRENCY="USD"');
        putenv('CACHE_ENABLE=false');
        putenv('CACHE_ENABLE_FILE="app/cache_enable_test.txt"');
        putenv('DEMO_STATUS=true'); // !!!! it is different from MenuTest
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
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function setTestData()
    {
        $this->objMenu = (app(MenuService::class))->wrapCreate($this->testData);
    }

    private function checkMethodInDemoVersion($response)
    {
        $response->assertStatus(403);

        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
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
        $this->checkMethodInDemoVersion($response);
        // $this->assertFalse($res->success);
    }

    public function test_it_will_wrong_add()
    {
        $testData2 =
      [
          'name' => ['en' => ''],
      ];

        $response = $this->post('api/menus?token='.$this->token, $testData2);
        $this->checkMethodInDemoVersion($response);
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
        $this->checkMethodInDemoVersion($response);
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

    public function test_it_will_show_all_menus()
    {
        $this->setTestData();

        $response = $this->get('api/menus?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(count($res->data), 1);

        $data = (array) $res->data[0];

        $mMenu = Menu::find($data['id']);
        $this->assertEquals((app(TranslationReader::class))->translatesByColumnAndLang($mMenu, 'name', 'en'), $data['name']->en);
        $this->assertSame((app(TranslationReader::class))->translatesByColumnAndLang($mMenu, 'name', 'en'), $this->testData['name']['en']);
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
        $this->checkMethodInDemoVersion($response);
    }

    public function test_it_will_update_menu_docs()
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
        $this->checkMethodInDemoVersion($response0);
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
        $this->checkMethodInDemoVersion($response0);

    }
}
