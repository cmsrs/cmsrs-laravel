<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Data\Demo;
use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\Cms\Page\PageService;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\HeadlessService;
use App\Services\Cmsrs\Shop\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HeadlessTest extends Base
{
    use RefreshDatabase;

    private const STR_DESC_IMG1 = 'description image 1';

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
        putenv('IS_HEADLESS=true');
        putenv('IS_SSR=false');

        parent::setUp();

        $this->createUser();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function prepareTestImages()
    {
        $name1 = 'phpunittest1.jpg';
        $file1 = $this->getFixtureBase64($name1);

        $images = [
            ['name' => $name1, 'data' => $file1, 'alt' => ['en' => self::STR_DESC_IMG1]],
        ];

        return $images;
    }

    public function test_it_will_get_first_page_by_short_title_without_auth_docs()
    {
        $type = 'inner';
        $shortTitle = 'main_page_box';
        $predefinedShortTitle = [$shortTitle.'1', $shortTitle.'2', $shortTitle.'3', 'company_data', 'main_page_slider'];
        $this->prepareTestDataForGetByType($type, $predefinedShortTitle);

        $res = $this->get('api/headless/pages-short-title/'.$shortTitle);
        $data = $res->getData();
        $this->assertTrue($data->success);

        $this->assertEquals($shortTitle.'1', $data->data[0]->short_title->en);
        $this->assertEquals($shortTitle.'2', $data->data[1]->short_title->en);
        $this->assertEquals($shortTitle.'3', $data->data[2]->short_title->en);
        $this->assertEquals(3, count($data->data));
    }

    public function test_it_will_get_all_pages_by_type_without_auth_docs()
    {
        $type = 'inner';
        $predefinedShortTitle = ['main_page_box1', 'main_page_box2', 'main_page_box3', 'company_data', 'main_page_slider'];
        $this->prepareTestDataForGetByType($type, $predefinedShortTitle);

        $resType = $this->get('api/headless/pages-type/'.$type);
        $data = $resType->getData();
        $this->assertTrue($data->success);

        $this->assertEquals(count($predefinedShortTitle), count($data->data));
        $this->assertEquals($predefinedShortTitle[0], $data->data[0]->short_title->en);
    }

    public function test_it_will_get_one_page_by_lang_without_auth_123() // old _docs
    {
        $lang = 'en';
        $testData =
        [
            'title' => ['en' => 'inner title'],
            'short_title' => ['en' => 'inner short_title'],
            'published' => 1,
            'type' => 'inner',
            'content' => ['en' => 'content test4333 inner'],
            'description' => ['en' => 'description test4333 inner'],
        ];

        $objPage = (app(PageService::class))->wrapCreate($testData);
        $this->assertNotEmpty($objPage->id);
        $res = $this->get('api/headless/page/'.$objPage->id.'/'.$lang);
        // dd($res->getContent());
        $data = $res->getData();
        $this->assertTrue($data->success);
        $this->assertEquals($testData['title']['en'], $data->data->title);
        $this->assertEquals($testData['content']['en'], $data->data->content);
        $this->assertEquals($testData['description']['en'], $data->data->description);
        $this->assertEquals($testData['short_title']['en'], $data->data->short_title);
        $this->assertTrue(! property_exists($data->data, 'products'));  // because it is not shop page, so products is empty  (type => inner)
    }

    public function test_it_will_get_one_page_by_lang_with_products_without_auth_docs()
    {
        $images = $this->prepareTestImages();
        $lang = 'en';
        $testData =
        [
            'title' => ['en' => 'shop title'],
            'short_title' => ['en' => ' short_title'],
            'published' => 1,
            'type' => 'shop',
            'content' => ['en' => 'content test4333 inner'],
            'description' => ['en' => 'description test4333 inner'],
            'images' => $images,
        ];

        $objPage = (app(PageService::class))->wrapCreate($testData);
        $this->assertNotEmpty($objPage->id);

        $testProduct = [
            'product_name' => ['en' => 'sportline socks'],
            'sku' => '1/234/100',
            'price' => 500,
            'published' => 1,
            'product_description' => ['en' => 'socks', 'pl' => 'skarpety'],
            'page_id' => $objPage->id,
            'images' => $images,
        ];

        $objProduct = app(ProductService::class)->wrapCreate($testProduct);
        $this->assertNotEmpty($objProduct->id);

        $res = $this->get('api/headless/page/'.$objPage->id.'/'.$lang);
        $data = $res->getData();

        $this->assertTrue($data->success);
        $this->assertTrue(property_exists($data->data, 'products'));
        $this->assertNotEmpty($data->data->products);
        $this->assertNotEmpty($data->data->products[0]);
        $this->assertEquals($testProduct['product_name']['en'], $data->data->products[0]->product_name);
        $this->assertEquals($testProduct['product_description']['en'], $data->data->products[0]->product_description);
        $this->assertEquals('sportline-socks', $data->data->products[0]->product_name_slug);
        $this->assertEquals($testProduct['price'], $data->data->products[0]->price);
        $this->assertEquals($testProduct['sku'], $data->data->products[0]->sku);
        $this->assertEquals(self::STR_DESC_IMG1, $data->data->images[0]->alt);
        $this->assertEquals(self::STR_DESC_IMG1, $data->data->products[0]->images[0]->alt);
    }

    public function test_it_will_get_one_page_by_lang_without_auth_service()
    {
        $testData =
        [
            'title' => ['en' => 'inner title'],
            'short_title' => ['en' => 'inner short_title'],
            'published' => 1,
            'type' => 'inner',
            'content' => ['en' => 'content test4333 inner'],
            'description' => ['en' => 'description test4333 inner'],
        ];

        $objPage = (app(PageService::class))->wrapCreate($testData);
        $this->assertNotEmpty($objPage->id);

        $page = app(HeadlessService::class)->getAllPagesWithImagesOneItemByLang($objPage, 'en');

        $this->assertEquals($testData['title']['en'], $page['title']);
        $this->assertEquals($testData['short_title']['en'], $page['short_title']);
        $this->assertEquals($testData['content']['en'], $page['content']);
        $this->assertEquals($testData['description']['en'], $page['description']);
    }

    /**
     * test ssr and headless together, because if it is headless
     */
    public function test_it_will_get_main_page_2_headless()
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
            'after_login' => 0,
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

        // because it is headless.
        $response = $this->get('/');
        $response->assertStatus(404);
    }

    public function test_it_will_get_all_menus_without_auth_docs()
    {
        (new Demo)->pagesAndMenu(true);
        $res = $this->get('api/headless/menus');
        // dd($res->getContent());
        $data = $res->getData();
        $this->assertTrue($data->success); // todo

        $this->assertEquals(5, count($data->data));
    }

    public function test_it_will_get_config_headless_docs()
    {
        $res = $this->get('api/headless/config');
        $data = $res->getData();
        $this->assertTrue($data->success);

        $this->assertEquals((new ConfigService)->getDefaultLang(), $data->data->default_lang);
        $this->assertEquals((new ConfigService)->arrGetLangs(), $data->data->langs);
    }
}
