<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Content;
use App\Models\Cmsrs\Translate;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Page\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentTest extends Base
{
    use RefreshDatabase;

    public $numOfLangs;

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

        $numOfLangs = app(ConfigService::class)->arrGetLangs();
        // $numOfLangsContent = app(ContentService::class)->getArrLangs();
        // $this->assertSame($numOfLangs, $numOfLangsContent);
        $this->assertEquals(2, count($numOfLangs));
        $this->numOfLangs = count($numOfLangs);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /*********************/
    /****general *********/
    /*********************/
    /*
        public function test_get_arr_langs()
        {
            $content = app(ContentService::class);
            $arrLangs = $content->getArrLangs();
            $this->assertTrue(is_array($arrLangs));
            $this->assertEquals(2, count($arrLangs));

            $configMock = Mockery::mock(ConfigService::class);
            $arrLangTest = ['en'];
            $configMock->shouldReceive('arrGetLangs')->andReturn($arrLangTest);
            $content2 = new ContentService($configMock);
            $arrLangs2 = $content2->getArrLangs();
            $this->assertSame($arrLangTest, $arrLangs2);
        }
    */

    /***********************/
    /**Content on the Page**/
    /***********************/

    public function test_page_content_wrap_create_ok_1()
    {
        $data1p = $this->getPageTestData();
        $p = (app(PageService::class))->wrapCreate($data1p);
        $this->assertNotEmpty($p->id);

        $this->assertEquals(2, Content::query()->where('page_id', $p->id)->count());
    }

    public function test_page_content_wrap_create_empty_1()
    {
        $data1p = $this->getPageTestData();
        unset($data1p['content']);

        $p = (app(PageService::class))->wrapCreate($data1p);
        $this->assertEquals($this->numOfLangs, Content::query()->where('page_id', $p->id)->count());
    }

    public function test_page_content_wrap_create_empty_2()
    {
        $data1p = $this->getPageTestData();
        $data1p['content'] = [];

        $p = (app(PageService::class))->wrapCreate($data1p);
        $this->assertEquals($this->numOfLangs, Content::query()->where('page_id', $p->id)->count());
    }

    public function test_page_content_wrap_create_empty_3()
    {
        $data1p = $this->getPageTestData();
        $data1p['content'] = ['es' => 'fake'];

        $p = (app(PageService::class))->wrapCreate($data1p);
        $this->assertEquals($this->numOfLangs, Content::query()->where('page_id', $p->id)->where('column', 'content')->whereNull('value')->count());
    }

    public function test_page_content_wrap_create_wrong()
    {
        $data1p = $this->getPageTestData();

        $data1p['content'] = 'str fake';
        $data1p['description'] = 'strereer';

        // $this->expectException(\Exception::class);
        $p = (app(PageService::class))->wrapCreate($data1p);
        $this->assertEquals($this->numOfLangs, Content::query()->where('page_id', $p->id)->where('column', 'content')->whereNull('value')->count());
        $this->assertEquals($this->numOfLangs, Translate::query()->where('page_id', $p->id)->where('column', 'description')->whereNull('value')->count());
    }
}
