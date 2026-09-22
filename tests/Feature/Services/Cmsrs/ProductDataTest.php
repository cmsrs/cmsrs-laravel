<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Shop\Product;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\PriceHelperService;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\Product\ProductDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductDataTest extends Base
{
    use RefreshDatabase;

    private $name1;

    private $name2;

    private $testData;

    private $testData2;

    private $testPage;

    private $testMenu;

    private $menuId;

    private $menuObj;

    private $pageId;

    private $priceDescription;

    const STR_DESC_IMG1 = 'description img1 - product image';

    const STR_PRODUCT_DESCRIPTION_EN = 'book desc';

    const STR_PRODUCT_NAME_EN = 'php3 db app';

    const STR_PRODUCT_NAME_EN_1 = 'name11';

    const INT_PRODUCT_PRICE = 123;

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

        $this->testMenu =
            [
                'name' => ['en' => 'books'],
            ];

        $this->name1 = 'phpunittest1.jpg';

        $this->name2 = 'phpunittest2.jpg';

        $this->priceDescription = app(PriceHelperService::class)->getPriceDescriptionWrap(self::INT_PRODUCT_PRICE);
        $this->assertNotEmpty($this->priceDescription);

        $pagination = ConfigService::getPagination(); // 10 - change .env.testing
        $this->assertEquals(10, $pagination);
        (new ConfigService)->createFileCacheEnableIfNotExist();
    }

    protected function tearDown(): void
    {
        (new ConfigService)->deleteFileCacheEnableIfExist();
        parent::tearDown();
    }

    private function setTestData()
    {
        $menu = (app(MenuService::class))->wrapCreate($this->testMenu);

        $this->menuObj = $menu; // $menu->all()->first();
        $this->menuId = $menu->id;  // $this->menuObj->id;
        $this->assertNotEmpty($this->menuId);

        $this->testPage =
        [
            'title' => ['en' => 'programmer'],
            'short_title' => ['en' => 'page1'],
            'published' => 1,
            'position' => 7,
            'type' => 'shop',
            'content' => ['en' => 'content test133445'],
            'menu_id' => $this->menuId,
        ];

        $p = (app(PageService::class))->wrapCreate($this->testPage);
        $this->assertNotEmpty($p->id);

        $type = 'shop';
        $res = $this->get('api/pages/type/'.$type.'?token='.$this->token);

        $data = $res->getData();
        $this->assertTrue($data->success);

        $this->pageId = $data->data[0]->id;
        $this->assertNotEmpty($this->pageId);
        $this->assertEquals($p->id, $this->pageId);

        $this->testData = [
            'product_name' => ['en' => self::STR_PRODUCT_NAME_EN],
            'sku' => 'AN/34534',
            'price' => self::INT_PRODUCT_PRICE,
            'product_description' => ['en' => self::STR_PRODUCT_DESCRIPTION_EN],
            'page_id' => $this->pageId,
            'published' => 1,
            'images' => [
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => self::STR_DESC_IMG1]],
                ['name' => $this->name2, 'data' => $this->getFixtureBase64($this->name2)],
            ],
        ];
    }

    /**
     * it is not test admin
     * this api is use in basket (it is useful when name and price will be changing)
     */
    public function test_it_will_get_name_and_price_by_lang_cache()
    {
        /* prepare data - start */
        $this->setTestData();
        $response0 = $this->post('api/products?token='.$this->token, $this->testData);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->testData['sku'] = 'uniq2';
        $this->testData['published'] = 1;
        $this->testData['product_name']['en'] = 'product name uniq en2';
        $response1 = $this->post('api/products?token='.$this->token, $this->testData);
        $res1 = $response1->getData();
        $this->assertTrue($res1->success);

        $countProd = Product::all()->count();
        $this->assertEquals(2, $countProd);

        $productArr = Product::all()->toArray();

        $pId1 = $productArr[0]['id'];
        $pId2 = $productArr[1]['id'];
        $arrIds = [$pId1, $pId2];
        /* prepare data - stop */

        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $lang = 'en';
        $productDataService = app(ProductDataService::class);
        $result1 = $productDataService->getAllProductsWithImagesByLangCache($lang);
        $this->assertEquals(count($arrIds), count($result1));

        $queriesAfterFirst = count(DB::getQueryLog());
        $this->assertTrue($queriesAfterFirst > 0); // czemu ten test nie przechodzi?
        // dump('first='. $queriesAfterFirst);

        DB::flushQueryLog(); // 🔥 reset między wywołaniami

        // 2nd call → should be cache
        $result2 = $productDataService->getAllProductsWithImagesByLangCache($lang);
        $queriesAfterSecond = count(DB::getQueryLog());

        $this->assertTrue($queriesAfterFirst > $queriesAfterSecond);

        $this->assertEquals($result1, $result2);

        // 🔥 KLUCZOWE
        $this->assertEquals(
            0,
            $queriesAfterSecond,
            'Second call should not hit database'
        );

    }
}
