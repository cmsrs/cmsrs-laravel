<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Enums\Cmsrs\SortDirection;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Content;
use App\Models\Cmsrs\Image;
use App\Models\Cmsrs\Shop\Basket;
use App\Models\Cmsrs\Shop\Checkout;
use App\Models\Cmsrs\Shop\Order;
use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Translate;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\DeliverService;
use App\Services\Cmsrs\Helpers\ArrObjHelperService;
use App\Services\Cmsrs\Helpers\PriceHelperService;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\PaymentService;
use App\Services\Cmsrs\Product\ProductDataService;
use App\Services\Cmsrs\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductTest extends Base
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

    }

    protected function tearDown(): void
    {
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
     * the same menu as setTestData
     */
    private function setTestData2($arrD = null)
    {
        // $menu = (new Menu)->wrapCreate($this->testMenu);
        // $this->menuObj = $menu; //$menu->all()->first();
        // $this->menuId = $menu->id;  // $this->menuObj->id;
        // $this->assertNotEmpty($this->menuId);

        $testPage =
        [
            'title' => ['en' => 'programmer2'],
            'short_title' => ['en' => 'page2'],
            'published' => 1,
            'position' => 7,
            'type' => 'shop',
            'content' => ['en' => 'content test13344522'],
            'menu_id' => $this->menuId,
        ];

        $p = (app(PageService::class))->wrapCreate($testPage);
        $this->assertNotEmpty($p->id);

        $type = 'shop';
        $res = $this->get('api/pages/type/'.$type.'?token='.$this->token);

        $data = $res->getData();

        $this->assertTrue($data->success);
        $pageId = null;
        foreach ($data->data as $item) {
            if ($item->title->en == $testPage['title']['en']) {
                $pageId = $item->id;
                break;
            }
        }

        $this->assertNotEmpty($pageId);

        // $data->data[1]
        // $pageId = $data->data[1]->id; //!!
        // $this->assertNotEmpty($this->pageId);
        // $this->assertEquals($p->id, $this->pageId);

        $this->testData2 = [
            'product_name' => ['en' => self::STR_PRODUCT_NAME_EN.' 2'],
            'sku' => ! empty($arrD['sku']) ? $arrD['sku'] : 'AN/34534_22',
            'price' => 123,
            'product_description' => ['en' => self::STR_PRODUCT_DESCRIPTION_EN.' 2'],
            'page_id' => $pageId,
            'published' => 1,
            'images' => [
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => self::STR_DESC_IMG1]],
                ['name' => $this->name2, 'data' => $this->getFixtureBase64($this->name2)],
            ],
        ];
    }

    public function test_it_will_search_products()
    {
        $price1 = 11200;
        $price2 = 32100;
        $ids = $this->setAddTwoProducts($price1, $price2);

        $key = self::STR_PRODUCT_NAME_EN_1;
        $lang = 'en';

        $objProducts = app(ProductService::class)->searchProducts($lang, $key);
        $arrProducts = ProductService::objToArray($objProducts);

        $this->assertEquals(1, count($arrProducts));
        $this->assertNotEmpty($arrProducts[0]);

        $products = app(ProductService::class)->wrapSearchProducts($lang, $key);
        $this->assertEquals(1, count($products));
    }

    private function simpleTestCheckoutData($data)
    {
        $priceHelperService = app(PriceHelperService::class);
        $this->assertNotEmpty($data->user_id);
        $this->assertNotEmpty($data->price_total);
        $this->assertEquals($data->price_total_description, $priceHelperService->getPriceDescriptionWrap($data->price_total));

        $this->assertNotEmpty($data->price_deliver);
        $this->assertEquals($data->price_deliver_description, $priceHelperService->getPriceDescriptionWrap($data->price_deliver));

        $this->assertNotEmpty($data->price_total_add_deliver);
        $this->assertEquals($data->price_total_add_deliver_description, $priceHelperService->getPriceDescriptionWrap($data->price_total_add_deliver));

        $this->assertNotEmpty($data->email);
        $this->assertNotEmpty($data->first_name);
        $this->assertNotEmpty($data->last_name);
        $this->assertNotEmpty($data->address);
        $this->assertNotEmpty($data->country);
        $this->assertNotEmpty($data->city);
        $this->assertNotEmpty($data->telephone);
        $this->assertNotEmpty($data->postcode);
        $this->assertEquals(0, $data->is_pay);
        $this->assertNotEmpty($data->created_at);

        $this->assertNotEmpty($data->baskets);
        $this->assertTrue(is_array($data->baskets));

        $this->assertNotEmpty($data->baskets[0]->qty);
        $this->assertNotEmpty($data->baskets[0]->price);
        $this->assertNotEmpty($data->baskets[0]->price_description);
        $this->assertEquals($data->baskets[0]->price_description, $priceHelperService->getPriceDescriptionWrap($data->baskets[0]->price));

        $this->assertNotEmpty($data->baskets[0]->product_id);
        $this->assertNotEmpty($data->baskets[0]->product_name);
        $this->assertNotEmpty($data->baskets[0]->product_url);
    }

    /**
     * api admin
     * it should be 2 tests - todo - get and update
     */
    public function test_it_will_get_and_update_checkouts_docs()
    {
        /*** set data - fixture */

        $price1 = 11200;
        $price2 = 32100;
        $ids = $this->setAddTwoProducts($price1, $price2);
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        $qty0a = 2;
        $qty1a = 5;
        $firstName = 'Jan';
        $data =

        [
            '_token' => 'gTXqPBuPTbTz1yKecuMiaX8j5ynB1LiO4ul01PwZ',
            'products' => [
                0 => [
                    'id' => $id1,
                    'qty' => $qty0a,
                ],

                1 => [
                    'id' => $id2,
                    'qty' => $qty1a,
                ],
                2 =>  // fake
                    [
                        'id' => 10003,
                        'qty' => 44,
                    ],
            ],

            'lang' => 'en',
            'email' => 'client@cmsrs.pl',
            'first_name' => $firstName,
            'last_name' => 'Kowalski',
            'address' => 'kolejowa 1 m 2',
            'country' => 'Polska',
            'city' => 'Warszawa',
            'telephone' => '1234567123',
            'postcode' => '03-456',
            'deliver' => DeliverService::KEY_DPD_COURIER,
            'payment' => PaymentService::KEY_CASH,
        ];

        // $pCheckout = [
        //     'title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'short_title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'checkout',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];

        // $pShoppingsuccess = [
        //     'title' => ['en' => 'CheckoutSS', 'pl' => 'KasaSS'],
        //     'short_title' => ['en' => 'CheckoutSS', 'pl' => 'KasaSS'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'shoppingsuccess',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];

        // $p = (new PageService)->wrapCreate($pCheckout);
        // $this->assertNotEmpty($p->id);

        // $p2 = (new PageService)->wrapCreate($pShoppingsuccess);
        // $this->assertNotEmpty($p2->id);

        $response0 = $this->post('/post/checkout', $data);
        $response0->assertStatus(302);

        $c1 = Checkout::all()->count();
        $this->assertEquals(1, $c1);

        $ch = Checkout::first();
        $this->assertEquals(0, $ch->is_pay);
        $this->assertNotEmpty($ch->id);

        /*** start testing */

        $response = $this->get('api/checkouts?token='.$this->token);
        $res = $response->getData();

        // dd($res);

        $this->assertTrue($res->success);
        $this->assertEquals($c1, count($res->data));

        $this->assertEquals($ch->id, $res->data[0]->id);
        $this->assertEquals(Auth::user()->id, $res->data[0]->user_id);

        $this->simpleTestCheckoutData($res->data[0]);

        // $this->assertNotEmpty($res->data[0]->user_id);
        // $this->assertNotEmpty($res->data[0]->price_total);
        // $this->assertEquals($res->data[0]->price_total_description, PriceHelperService::getPriceDescriptionWrap($res->data[0]->price_total) );

        // $this->assertNotEmpty($res->data[0]->price_deliver);
        // $this->assertEquals($res->data[0]->price_deliver_description, PriceHelperService::getPriceDescriptionWrap($res->data[0]->price_deliver) );

        // $this->assertNotEmpty($res->data[0]->price_total_add_deliver);
        // $this->assertEquals($res->data[0]->price_total_add_deliver_description, PriceHelperService::getPriceDescriptionWrap($res->data[0]->price_total_add_deliver) );

        // $this->assertNotEmpty($res->data[0]->email);
        // $this->assertNotEmpty($res->data[0]->first_name);
        // $this->assertNotEmpty($res->data[0]->last_name);
        // $this->assertNotEmpty($res->data[0]->address);
        // $this->assertNotEmpty($res->data[0]->country);
        // $this->assertNotEmpty($res->data[0]->city);
        // $this->assertNotEmpty($res->data[0]->telephone);
        // $this->assertNotEmpty($res->data[0]->postcode);
        // $this->assertEquals(0, $res->data[0]->is_pay);
        // $this->assertNotEmpty($res->data[0]->created_at);

        // $this->assertNotEmpty($res->data[0]->baskets);
        // $this->assertTrue(is_array($res->data[0]->baskets));

        // $this->assertNotEmpty($res->data[0]->baskets[0]->qty);
        // $this->assertNotEmpty($res->data[0]->baskets[0]->price);
        // $this->assertNotEmpty($res->data[0]->baskets[0]->price_description);
        // $this->assertEquals($res->data[0]->baskets[0]->price_description, PriceHelperService::getPriceDescriptionWrap($res->data[0]->baskets[0]->price) );

        // $this->assertNotEmpty($res->data[0]->baskets[0]->product_id);
        // $this->assertNotEmpty($res->data[0]->baskets[0]->product_name);
        // $this->assertNotEmpty($res->data[0]->baskets[0]->product_url);

        $orders = Order::all()->toArray();
        $this->assertEmpty($orders);

        $dataUpdate = [
            'is_pay' => 1,
        ];

        $response2 = $this->patch('api/checkouts/'.$ch->id.'?token='.$this->token, $dataUpdate);
        $res2 = $response2->getData();

        $this->assertTrue($res2->success);

        $orders = Order::all()->toArray();
        $this->assertNotEmpty($orders);

        $this->assertEquals(2, count($orders));

        // the same !!!
        foreach ($orders as $o) {

            $this->assertTrue(in_array($o['product_id'], $ids));
            if ($o['product_id'] == $ids['id1']) {
                $this->assertEquals($qty0a, $o['qty']);
            } elseif ($o['product_id'] == $ids['id2']) {
                $this->assertEquals($qty1a, $o['qty']);
            } else {
                $this->assertTrue(false); // this case is imposible
            }
        }

        // second time update
        $response2b = $this->patch('api/checkouts/'.$ch->id.'?token='.$this->token, $dataUpdate);
        $res2b = $response2b->getData();
        $this->assertTrue($res2b->success);

        $orders = Order::all()->toArray();
        $this->assertNotEmpty($orders);

        $this->assertEquals(2, count($orders));

        // the same !!!
        foreach ($orders as $o) {

            $this->assertTrue(in_array($o['product_id'], $ids));
            if ($o['product_id'] == $ids['id1']) {
                $this->assertEquals($qty0a, $o['qty']);
            } elseif ($o['product_id'] == $ids['id2']) {
                $this->assertEquals($qty1a, $o['qty']);
            } else {
                $this->assertTrue(false); // this case is impossible
            }
        }

        $response3 = $this->get('api/checkouts?token='.$this->token);
        $res3 = $response3->getData();
        $this->assertTrue($res3->success);
        $this->assertEquals($c1, count($res3->data));

        $this->assertEquals($ch->id, $res3->data[0]->id);
        $this->assertEquals($dataUpdate['is_pay'], $res3->data[0]->is_pay);

        $response0Next = $this->post('/post/checkout', $data);
        $response0->assertStatus(302);

        $c2 = Checkout::all()->count();
        $this->assertEquals(2, $c2);
        $chLast = Checkout::all()->reverse()->first();

        $this->assertNotEquals($ch->id, $chLast->id);

        $response2c = $this->patch('api/checkouts/'.$chLast->id.'?token='.$this->token, $dataUpdate);
        $res2c = $response2c->getData();
        $this->assertTrue($res2c->success);

        $orders = Order::all()->toArray();
        $this->assertNotEmpty($orders);

        $this->assertEquals(2, count($orders));

        // NOT the same !!!
        foreach ($orders as $o) {

            $this->assertTrue(in_array($o['product_id'], $ids));
            if ($o['product_id'] == $ids['id1']) {
                $this->assertEquals(2 * $qty0a, $o['qty']); // 2x
            } elseif ($o['product_id'] == $ids['id2']) {
                $this->assertEquals(2 * $qty1a, $o['qty']); // 2x
            } else {
                $this->assertTrue(false); // this case is impossible
            }
        }

    }

    public function test_delete_page_with_checkouts_belongs_to_product()
    {
        /*** set data - fixture */

        $price1 = 11200;
        $price2 = 32100;
        $ids = $this->setAddTwoProducts($price1, $price2);
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        $qty0a = 2;
        $qty1a = 5;
        $firstName = 'Jan';
        $data =

        [
            '_token' => 'gTXqPBuPTbTz1yKecuMiaX8j5ynB1LiO4ul01PwZ',
            'products' => [
                0 => [
                    'id' => $id1,
                    'qty' => $qty0a,
                ],

                1 => [
                    'id' => $id2,
                    'qty' => $qty1a,
                ],
                2 =>  // fake
                    [
                        'id' => 10003,
                        'qty' => 44,
                    ],
            ],

            'lang' => 'en',
            'email' => 'client@cmsrs.pl',
            'first_name' => $firstName,
            'last_name' => 'Kowalski',
            'address' => 'kolejowa 1 m 2',
            'country' => 'Polska',
            'city' => 'Warszawa',
            'telephone' => '1234567123',
            'postcode' => '03-456',
            'deliver' => DeliverService::KEY_DPD_COURIER,
            'payment' => PaymentService::KEY_CASH,
        ];

        // $pCheckout = [
        //     'title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'short_title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'checkout',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];

        // $pShoppingsuccess = [
        //     'title' => ['en' => 'CheckoutSS', 'pl' => 'KasaSS'],
        //     'short_title' => ['en' => 'CheckoutSS', 'pl' => 'KasaSS'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'shoppingsuccess',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];

        // $p = (new PageService)->wrapCreate($pCheckout);
        // $this->assertNotEmpty($p->id);

        // $p2 = (new PageService)->wrapCreate($pShoppingsuccess);
        // $this->assertNotEmpty($p2->id);

        $response0 = $this->post('/post/checkout', $data);
        $response0->assertStatus(302);

        $c1 = Checkout::all()->count();
        $this->assertEquals(1, $c1);

        $ch = Checkout::first();
        $this->assertEquals(0, $ch->is_pay);
        $this->assertNotEmpty($ch->id);

        /*** start testing */

        $baskets = Basket::all()->toArray();
        $this->assertEquals(2, count($baskets));

        $products = Product::all()->toArray();
        $this->assertEquals(2, count($products));
        $this->assertEquals($products[0]['page_id'], $products[1]['page_id']);
        $pageId = $products[0]['page_id'];

        $response0 = $this->delete('api/pages/'.$pageId.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $c2 = Checkout::all()->count();
        $this->assertEquals(1, $c2); // ?? - TODO in future, maybe it should be 0

        $baskets2 = Basket::all()->toArray();
        $this->assertEquals(0, count($baskets2));

        $products2 = Product::all()->toArray();
        $this->assertEquals(0, count($products2));
    }

    private function warpSaveTestCheckoutManyTimes($times)
    {
        $price1 = 11200;
        $price2 = 32100;
        $ids = $this->setAddTwoProducts($price1, $price2);

        $userId = $this->createClientUser()->id; // Auth::check() ? Auth::user()->id : null;
        $this->assertNotEmpty($userId);

        $out = [];
        for ($i = 0; $i < $times; $i++) {
            $out[$i]['objCheckout'] = $this->saveTestCheckout($ids, $userId, $i)['objCheckout'];
        }

        return $out;
    }

    private function saveTestCheckout($ids, $userId, $i)
    {
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        $qty0a = 2 + $i;
        $qty1a = 5 + $i;
        $firstName = 'Jan';

        $data =

        [
            // '_token' => 'gTXqPBuPTbTz1yKecuMiaX8j5ynB1LiO4ul01PwZ',
            'products' => [
                0 => [
                    'id' => $id1,
                    'qty' => $qty0a,
                ],

                1 => [
                    'id' => $id2,
                    'qty' => $qty1a,
                ],
                2 =>  // fake
                    [
                        'id' => 10003,
                        'qty' => 44,
                    ],
            ],
            'lang' => 'en',
            'email' => "client$i@cmsrs.pl",
            'first_name' => $firstName,
            'last_name' => 'Kowalski',
            'address' => "kolejowa $i m 2",
            'country' => 'Polska',
            'city' => 'Warszawa',
            'telephone' => '1234567123',
            'postcode' => '03-456',
            'deliver' => DeliverService::KEY_DPD_COURIER,
            'payment' => PaymentService::KEY_CASH,
        ];

        $sessionId = 123 + $i; // session()->getId()

        [
            'productsDataAndTotalAmount' => $productsDataAndTotalAmount,
            'checkout' => $checkout,
            'objCheckout' => $objCheckout
        ] = app(ProductService::class)->saveCheckout($data, $userId, $sessionId);
        $this->assertNotEmpty($objCheckout->id);

        return [
            'objCheckout' => $objCheckout,
        ];
    }

    public function test_save_checkout_directly_without_post()
    {
        $c0 = Checkout::all()->count();
        $this->assertEquals(0, $c0);

        $times = 10;
        $out = $this->warpSaveTestCheckoutManyTimes($times);
        $this->assertEquals($times, count($out));

        $c1 = Checkout::all()->count();
        $this->assertEquals($times, $c1);

        $ch = Checkout::first();
        $this->assertEquals($ch->id, $out[0]['objCheckout']->id);
    }

    /**
     * it is not test admin
     * process of buying products
     */
    public function test_it_will_save_checkout()
    {
        $price1 = 11200;
        $price2 = 32100;
        $ids = $this->setAddTwoProducts($price1, $price2);
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        $user = Auth::user();
        $this->assertNotEmpty($user->id);

        // $pShoppingSuccess = [
        //     'title' => ['en' => 'Shopping Success', 'pl' => 'Twoje zakupy'],
        //     'short_title' => ['en' => 'Shopping Success', 'pl' => 'Twoje zakupy'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'shoppingsuccess',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // (new PageService)->wrapCreate($pShoppingSuccess);

        $qty0a = 2;
        $qty1a = 5;
        $firstName = 'Jan';
        $data =

        [
            '_token' => 'gTXqPBuPTbTz1yKecuMiaX8j5ynB1LiO4ul01PwZ',
            'products' => [
                0 => [
                    'id' => $id1,
                    'qty' => $qty0a,
                ],

                1 => [
                    'id' => $id2,
                    'qty' => $qty1a,
                ],
                2 =>  // fake
                    [
                        'id' => 10003,
                        'qty' => 44,
                    ],
            ],

            'lang' => 'en',
            'email' => 'client@cmsrs.pl',
            'first_name' => $firstName,
            'last_name' => 'Kowalski',
            'address' => 'kolejowa 1 m 2',
            'country' => 'Polska',
            'city' => 'Warszawa',
            'telephone' => '1234567123',
            'postcode' => '03-456',
            'deliver' => DeliverService::KEY_DPD_COURIER,
            'payment' => PaymentService::KEY_CASH,
        ];

        // $pCheckout = [
        //     'title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'short_title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'checkout',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];

        // $pShoppingsuccess = [
        //     'title' => ['en' => 'CheckoutSS', 'pl' => 'KasaSS'],
        //     'short_title' => ['en' => 'CheckoutSS', 'pl' => 'KasaSS'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'shoppingsuccess',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];

        $c0 = Checkout::all()->count();
        $this->assertEquals(0, $c0);

        // $p = (new PageService)->wrapCreate($pCheckout);
        // $this->assertNotEmpty($p->id);
        // $p2 = (new PageService)->wrapCreate($pShoppingsuccess);
        // $this->assertNotEmpty($p2->id);

        // $c100 = Checkout::all()->count();
        // $this->assertEquals(0, $c100);

        $response0 = $this->post('/post/checkout', $data);
        $response0->assertStatus(302);

        $c1 = Checkout::all()->count();
        $this->assertEquals(1, $c1);

        $ch = Checkout::first(); // ->toArray();

        $this->assertEquals(0, $ch->is_pay);
        $this->assertEquals($user->id, $ch->user_id);
        $this->assertEquals($firstName, $ch->first_name);

        $this->assertNotEmpty($ch->price_total);
        $this->assertNotEmpty($ch->price_deliver);
        $this->assertNotEmpty($ch->price_total_add_deliver);
        $this->assertEquals($ch->price_total + $ch->price_deliver, $ch->price_total_add_deliver);

        $this->assertNotEmpty($ch->session_id);
        $sessionId = session()->getId();
        $this->assertEquals($sessionId, $ch->session_id);

        $this->assertEquals(2, $ch->baskets->count());
        foreach ($ch->baskets as $basket) {
            $b = $basket->toArray();
            $this->assertTrue(in_array($b['product_id'], $ids));
            if ($b['product_id'] == $ids['id1']) {
                $this->assertEquals($price1, $b['price']);
            }
            if ($b['product_id'] == $ids['id2']) {
                $this->assertEquals($price2, $b['price']);
            }

        }

        // $o0 = Order::all()->count();
        // $this->assertEquals(0, $o0);
        // $isCopy0 =  Order::copyDataFromBasketToOrderForUser($ch);
        // $this->assertTrue($isCopy0);

        // $o1count = Order::all()->count();
        // $this->assertEquals(2, $o1count);
        // print_r(Order::all()->toArray());

        $ch1 = Checkout::first(); // ->toArray();
        $this->assertEquals(0, $ch1->is_pay);

        // second process payment
        $qty1b = 32;
        $data['products'] = [
            [
                'id' => $id2,
                'qty' => $qty1b,
            ],
        ];
        $response1 = $this->post('/post/checkout', $data);
        $response1->assertStatus(302);
        $this->assertEquals(2, Checkout::all()->count());

        // $ch = app(CheckoutService::class)->findActiveOrder();
        // $this->assertNotEmpty($ch);
        // $this->assertEquals(0, $ch->is_pay);

        // pShoppingSuccess
        // $pSuc = PageService::getFirstPageByType('shoppingsuccess');
        // $this->assertNotEmpty($pSuc);
        $urlShoppingSuccess = route('shoppingsuccess', ['lang' => 'en']);  // (new PageService)->getUrl($pSuc, 'en');
        $response3 = $this->get($urlShoppingSuccess);
        $response3->assertStatus(200); // because there is checkout_id in session therefore is 200 status

        // $ret1 =  Order::copyDataFromBasketToOrderForUser();
        // $this->assertTrue($ret1);
        // $ch2 = Checkout::findActiveOrder();
        // $this->assertEmpty($ch2);

        // $ret1b = Order::copyDataFromBasketToOrderForUser();
        // $this->assertFalse($ret1b);

        // $o2count = Order::all()->count();
        // $this->assertEquals($o2count, $o1count);

        // $arrOrders = Order::all()->toArray();
        // $this->assertEquals($o2count, count($arrOrders) );

        // //print_r($arrOrders);
        // foreach($arrOrders as $o){
        //     $this->assertTrue( in_array( $o['product_id'], $ids ) );
        //     if( $o['product_id'] == $ids['id1'] ){
        //         $this->assertEquals($qty0a, $o['qty']);
        //     }
        //     if( $o['product_id'] == $ids['id2'] ){
        //         $this->assertEquals( ($qty1a + $qty1b), $o['qty'] );
        //     }
        // }

    }

    /**
     * it is not test admin
     * this api is use in basket (it is useful when name and price will be changing)
     */
    public function test_it_will_get_name_and_price_by_lang_docs()
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

        $lang = 'en';
        $url = 'api/productsGetNameAndPrice/'.$lang;
        $response = $this->get($url);

        $data = $response->getData();
        $this->assertTrue($data->success);

        $dd = (array) $data->data;
        $this->assertEquals(count($arrIds), count($dd));

        foreach ($arrIds as $pId) {
            $this->assertNotEmpty($data->data->$pId->price);
            $this->assertNotEmpty($data->data->$pId->name);
            $this->assertNotEmpty($data->data->$pId->url_product);
            $this->assertNotEmpty($data->data->$pId->url_image);
            // $this->assertNotEmpty($data->data->$pId->product_id);
            // $this->assertEquals($data->data->$pId->product_id, $pId);
        }

        $lang = '';
        $response = $this->get('api/productsGetNameAndPrice/'.$lang);
        $data2 = $response->getData();
        $this->assertTrue($data2->success);
        $this->assertEquals($data, $data2);

    }

    private function setAddTwoProducts($price1 = 11200, $price2 = 32100)
    {

        $this->setTestData();
        // it must be 2 product in this test!!!
        $testData1 = $this->testData;
        $testData2 = $this->testData;

        $testData1['sku'] = '11';
        $testData1['price'] = $price1;
        $testData1['product_name']['en'] = self::STR_PRODUCT_NAME_EN_1;  // 'name11';
        $r0 = $this->post('api/products?token='.$this->token, $testData1);
        $this->assertTrue($r0->getData()->success);
        $testData2['sku'] = '22';
        $testData2['price'] = $price2;
        $testData2['product_name']['en'] = 'name22';
        $r1 = $this->post('api/products?token='.$this->token, $testData2);
        $this->assertTrue($r1->getData()->success);

        $products = Product::all()->toArray();
        $this->assertEquals(2, count($products));

        $id1 = $products[0]['id'];
        $id2 = $products[1]['id'];

        return ['id1' => $id1, 'id2' => $id2];
    }

    /**
     * it is not test admin
     * it tests home/api/tobank
     */
    public function test_it_will_get_products_and_total_amount()
    {
        $price1 = 11200;
        $price2 = 32100;

        $qty1 = 10;
        $qty2 = 5;

        $ids = $this->setAddTwoProducts($price1, $price2);
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        /*
        name and price is not important in this post
        */
        $json =
        '{
            "cart": [
                {
                    "id": '.$id1.',
                    "name": "PHP3 aplikacje bazodanowe - xxxxxxxxxxxxxxxxxxxxxxxyyyyyyyyyyyyyy",
                    "price": 119999999999999999999,
                    "qty": '.$qty1.'
                },
                {
                    "id": '.$id2.',
                    "name": "PHP5 nott imporstentttttttttttt",
                    "price": 30999999999999,
                    "qty": '.$qty2.'
                }
            ]
        }';

        $obj = json_decode($json);
        $this->assertEquals(2, count($obj->cart));

        $arrCart = ArrObjHelperService::reIndexArr($obj->cart);

        $productService = app(ProductService::class);
        $data = $productService->createPaymentData($arrCart);
        $baskets = $data['baskets'];
        $orders = $data['orders'];

        // dd($data);

        $this->assertEquals(2, count($baskets));

        $this->assertNotEmpty($orders); // right now
        // $this->assertEmpty($orders); //TODO - it is useless tests (but it was before refactor, so I will comment it for now) - in code i don't use this feature

        $this->assertEquals(2, count($data['products']));
        $this->assertEquals(($price1 * $qty1 + $price2 * $qty2), $data['totalAmount']);

        $this->assertNotEmpty($data['products'][0]['name']);
        $this->assertEquals(self::STR_PRODUCT_NAME_EN_1, $data['products'][0]['name']);
        $this->assertEquals($qty1, $data['products'][0]['quantity']);
        $this->assertEquals($price1, $data['products'][0]['unitPrice']);

        $this->assertNotEmpty($data['products'][1]['name']);
        $this->assertEquals($qty2, $data['products'][1]['quantity']);
        $this->assertEquals($price2, $data['products'][1]['unitPrice']);

        $dd = $productService->createPaymentData($arrCart);
        $baskets2 = $dd['baskets'];
        $orders2 = $dd['orders'];

        $this->assertNotEmpty($baskets2); // right now
        // $this->assertEmpty($baskets2);//TODO - it is useless tests (but it was before refactor, so I will comment it for now) - in code i don't use this feature

        $this->assertTrue(is_array($orders2));
        $this->assertEquals(2, count($orders2));
        foreach ($orders2 as $order2) {
            $this->assertNotEmpty($order2['product_url']);
            $this->assertNotEmpty($order2['product_img']);
        }
    }

    public function test_it_will_create_product_with_images_by_page()
    {
        $this->setTestData();

        $response0 = $this->post('api/products?token='.$this->token, $this->testData);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->testData['sku'] = 'uniq2';
        $this->testData['published'] = 0;
        $this->testData['product_name']['en'] = 'product name uniq en2';
        $response1 = $this->post('api/products?token='.$this->token, $this->testData);
        $res1 = $response1->getData();
        $this->assertTrue($res1->success);

        $countProd = Product::all()->count();
        $this->assertEquals(2, $countProd);

        $products = app(ProductDataService::class)->getProductsWithImagesByPage($this->pageId);
        $this->assertEquals(1, count($products));
        $this->assertEquals($this->pageId, $products[0]['page_id']);
        $this->assertEquals(1, $products[0]['published']);
        $this->assertEquals(self::STR_PRODUCT_NAME_EN, $products[0]['product_name']['en']);

        // $urls = (new Product)->getProductsUrl();
        // $this->assertEquals(1, count($urls));
        // $this->assertNotEmpty($urls[0]['en']);
        // $response = $this->get($urls[0]['en']);
        // $response->assertStatus(200);
    }

    public function test_it_will_unpublish_one_page_in_menu_product()
    {
        $this->setTestData();
        // $this->setTestData2();

        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();

        $this->assertTrue($res0->success);

        $urls = app(ProductService::class)->getProductsUrl();

        $this->assertEquals(1, count($urls));
        $prodUrl = $urls[0]['en'];
        $this->assertNotEmpty($prodUrl);
        $response = $this->get($prodUrl);
        $response->assertStatus(200);

        $response22 = $this->get('api/products?token='.$this->token);
        $res22 = $response22->getData();
        $productId = $res22->data[0]->id;

        $this->testData['published'] = 0;
        $response2 = $this->put('api/products/'.$productId.'?token='.$this->token, $this->testData);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $products = Product::all()->toArray();
        $this->assertEquals(0, $products[0]['published']);
        $urls2 = app(ProductService::class)->getProductsUrl();
        $this->assertEmpty($urls2);

        $response2 = $this->get($prodUrl);
        $response2->assertStatus(404);
    }

    public function test_it_will_unpublish_product()
    {
        $this->setTestData();
        $this->setTestData2();

        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();

        $this->assertTrue($res0->success);

        $urls = app(ProductService::class)->getProductsUrl();

        $this->assertEquals(1, count($urls));
        $prodUrl = $urls[0]['en'];
        $this->assertNotEmpty($prodUrl);
        $response = $this->get($prodUrl);
        $response->assertStatus(200);

        $response22 = $this->get('api/products?token='.$this->token);
        $res22 = $response22->getData();
        $productId = $res22->data[0]->id;

        $this->testData['published'] = 0;
        $response2 = $this->put('api/products/'.$productId.'?token='.$this->token, $this->testData);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $products = Product::all()->toArray();
        $this->assertEquals(0, $products[0]['published']);
        $urls2 = app(ProductService::class)->getProductsUrl();
        $this->assertEmpty($urls2);

        $response2 = $this->get($prodUrl);
        $response2->assertStatus(404);
    }

    public function test_it_will_unpublish_page()
    {
        $this->setTestData();

        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();

        $this->assertTrue($res0->success);

        $urls = app(ProductService::class)->getProductsUrl();
        $this->assertEquals(1, count($urls));
        $prodUrl = $urls[0]['en'];
        $this->assertNotEmpty($prodUrl);

        // $response = $this->get($prodUrl);
        // $response->assertStatus(200);

        $page = Page::all()->first();
        $page->published = 0;
        $page->save();

        // $page2 = Page::all()->toArray();
        // dd($page2);

        $response2 = $this->get($prodUrl);
        $response2->assertStatus(404);
    }

    /**
     * similar function to: it_will_get_product_by_slug
     * except one page belongs to menu
     */
    public function test_it_will_get_one_product_in_menu_by_slug()
    {
        $productService = app(ProductService::class);
        $this->setTestData();
        // $this->setTestData2();

        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $productId = $res0->data->productId;
        $this->assertNotEmpty($productId);

        $lang = 'en';
        $slugProductName = Str::slug(self::STR_PRODUCT_NAME_EN, '-');
        $product = $productService->getProductBySlug($slugProductName, $lang);

        $this->assertNotEmpty($product);

        $this->assertEquals($productId, $product['id']);

        $urls = app(ProductDataService::class)->getProductUrls($product);
        $this->assertNotEmpty($urls);

        $urlCategory = $urls['url_category']['en'];
        $urlProduct = $urls['url_product']['en'];

        $response = $this->get($urlCategory);
        $response->assertStatus(200);

        $response = $this->get($urlProduct);
        $response->assertStatus(200);

        $product2 = $productService->getProductBySlug('fake', $lang);
        $this->assertEquals(null, $product2);
    }

    public function test_it_will_get_product_by_slug()
    {
        $this->setTestData();
        $this->setTestData2();

        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $productId = $res0->data->productId;
        $this->assertNotEmpty($productId);

        $lang = 'en';
        $slugProductName = Str::slug(self::STR_PRODUCT_NAME_EN, '-');
        $product = app(ProductService::class)->getProductBySlug($slugProductName, $lang);

        $this->assertNotEmpty($product);

        $this->assertEquals($productId, $product['id']);

        $urls = app(ProductDataService::class)->getProductUrls($product);
        $this->assertNotEmpty($urls);

        $urlCategory = $urls['url_category']['en'];
        $urlProduct = $urls['url_product']['en'];

        $response = $this->get($urlCategory);
        $response->assertStatus(200);

        $response = $this->get($urlProduct);
        $response->assertStatus(200);

        $product2 = app(ProductService::class)->getProductBySlug('fake', $lang);
        $this->assertEquals(null, $product2);
    }

    public function test_it_will_check_fixtures_get_pages_by_type()
    {
        $this->setTestData();
        $type = 'shop';
        $res = $this->get('api/pages/type/'.$type.'?token='.$this->token);

        $data = $res->getData();
        $this->assertEquals($data->data[0]->title->en, $this->testPage['title']['en']);
    }

    public function test_it_will_check_uniq_product_name_add_action() // error
    {
        $this->setTestData();
        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->testData['sku'] = 'uniq2';
        $response1 = $this->post('api/products?token='.$this->token, $this->testData);

        $res1 = $response1->getData();

        $this->assertFalse($res1->success);
        $this->assertNotEmpty($res1->error);
        $this->assertTrue(strpos($res1->error->product_name, 'Duplicate product name') === 0);
    }

    public function test_it_add_product_case1_error_docs() // error
    {
        $this->setTestData();
        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->testData['price'] = '';
        $response1 = $this->post('api/products?token='.$this->token, $this->testData);

        $res1 = $response1->getData();

        $this->assertFalse($res1->success);
        $this->assertNotEmpty($res1->error->sku[0]);
        $this->assertNotEmpty($res1->error->price[0]);
        $this->assertNotEmpty($res1->error->price[1]);
    }

    public function test_it_add_product_case2_error_docs() // error
    {
        $this->setTestData();
        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->testData['sku'] = 'uniq1';
        $this->testData['price'] = '1';
        $response1 = $this->post('api/products?token='.$this->token, $this->testData);

        $res1 = $response1->getData();

        $this->assertFalse($res1->success);
        $this->assertTrue(strpos($res1->error->product_name, 'Duplicate product name') === 0);
    }

    public function test_it_will_check_uniq_product_name_update_action()
    {
        $this->setTestData();
        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->testData['sku'] = 'uniq1';
        $this->testData['product_name']['en'] = 'product name uniq en';
        $response1 = $this->post('api/products?token='.$this->token, $this->testData);

        $res1 = $response1->getData();
        $this->assertTrue($res1->success);

        $productId = $res1->data->productId;
        $this->assertNotEmpty($productId);

        $this->testData['sku'] = 'uniq2';
        $this->testData['product_name']['en'] = self::STR_PRODUCT_NAME_EN;
        $response2 = $this->put('api/products/'.$productId.'?token='.$this->token, $this->testData);

        $res2 = $response2->getData();

        $this->assertFalse($res2->success);
        $this->assertNotEmpty($res2->error);
        $this->assertTrue(strpos($res2->error->product_name, 'Duplicate product name') === 0);
    }

    public function test_it_will_create_product_docs()
    {
        $this->setTestData();

        $this->testData['published'] = 0;
        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();

        $this->assertTrue($res0->success);
        $this->assertNotEmpty($res0->data->productId);

        $this->assertEquals(self::INT_PRODUCT_PRICE, $res0->data->data->price);
        $this->assertEquals($this->priceDescription, $res0->data->data->price_description);

        $products = Product::all()->toArray();
        $this->assertEquals(count($products), 1);
        $this->assertEquals($products[0]['id'], $res0->data->productId);
        $this->assertEquals(0, $products[0]['published']);
        $this->assertEquals(0, $res0->data->data->published);
        $this->assertEquals($products[0]['published'], $res0->data->data->published);

        // Translate::query()->where('page_id', $p->id)->get()->toArray();
        $trans = Content::query()->where('product_id', $res0->data->productId)->get()->toArray();
        $this->assertEquals(count($trans), 1);

        $this->assertNotEmpty($this->testData['product_description']['en']);
        $this->assertEquals(self::STR_PRODUCT_DESCRIPTION_EN, $this->testData['product_description']['en']);

        $this->assertEquals('en', $trans[0]['lang']);
        $this->assertEquals('product_description', $trans[0]['column']);
        $this->assertEquals($res0->data->productId, $trans[0]['product_id']);
        $this->assertEquals(self::STR_PRODUCT_DESCRIPTION_EN, $trans[0]['value']);

        $trans2 = Translate::query()->where('product_id', $res0->data->productId)->get()->toArray();
        $this->assertEquals(count($trans2), 1);

        $this->assertNotEmpty($this->testData['product_name']['en']);
        $this->assertEquals(self::STR_PRODUCT_NAME_EN, $this->testData['product_name']['en']);

        // $this->assertEquals( self::STR_PRODUCT_DESCRIPTION_EN, $this->testData['product_description']['en']);
        $this->assertEquals('en', $trans2[0]['lang']);
        $this->assertEquals('product_name', $trans2[0]['column']);
        $this->assertEquals($res0->data->productId, $trans2[0]['product_id']);
        $this->assertEquals($this->testData['product_name']['en'], $trans2[0]['value']);

        $response = $this->post('api/products?token='.$this->token, $this->testData);

        $res = $response->getData();

        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
        $this->assertNotEmpty($res->error->sku);

    }

    public function test_it_will_read_product_docs()
    {
        $this->setTestData();
        $res0 = $this->post('api/products?token='.$this->token, $this->testData);
        $res = $res0->getData();

        $this->assertTrue($res->success);

        $response22 = $this->get('api/products?token='.$this->token);

        $res22 = $response22->getData();

        $this->assertTrue($res22->success);
        $this->assertEquals(count($res22->data), 1);

        $this->assertEquals(self::INT_PRODUCT_PRICE, $res22->data[0]->price);
        $this->assertEquals($this->priceDescription, $res22->data[0]->price_description);

        $this->assertTrue(isset($res22->data[0]->product_description));
        $this->assertEquals(self::STR_PRODUCT_DESCRIPTION_EN, $res22->data[0]->product_description->en);

        $this->assertEquals(1, $res22->data[0]->published);

        $this->assertTrue(isset($res22->data[0]->product_name));
        $this->assertEquals(self::STR_PRODUCT_NAME_EN, $res22->data[0]->product_name->en);

        $this->assertEquals(self::STR_PRODUCT_NAME_EN, $res22->data[0]->product_name_default_lang);

        $this->assertEquals($res->data->productId, $res22->data[0]->id);

        $products = Product::all()->toArray();
        $this->assertEquals(1, count($products));

        $this->assertEquals($res22->data[0]->sku, $this->testData['sku']);
        $this->assertNotEmpty($res22->data[0]->id);

        $this->assertEquals(count($res22->data[0]->images), 2);
        $this->assertEquals($res22->data[0]->images[0]->name, $this->name1);
        $this->assertEquals($res22->data[0]->images[1]->name, $this->name2);

        $this->assertFileExists(public_path().'/'.$res22->data[0]->images[1]->fs->medium);

        $this->assertIsInt($res22->data[0]->id);
        $this->assertIsInt($res22->data[0]->price);
        $this->assertIsInt($res22->data[0]->page_id);
        $this->assertIsInt($res22->data[0]->images[0]->position);
        $this->assertIsInt($res22->data[0]->images[0]->product_id);

        $this->assertEquals(self::STR_DESC_IMG1, $res22->data[0]->images[0]->alt->en);

        $this->assertEquals(null, $res22->data[0]->images[1]->alt->en);

    }

    public function test_it_will_update_product_docs()
    {
        $this->setTestData();

        $res0 = $this->post('api/products?token='.$this->token, $this->testData);
        $res = $res0->getData();

        $response22 = $this->get('api/products?token='.$this->token);
        $res22 = $response22->getData();
        $productId = $res22->data[0]->id;
        $oldImages = $res22->data[0]->images;
        $this->assertEquals(count($oldImages), 2);

        $arrOldImage = [];
        $i = 1;
        foreach ($oldImages as $img) {
            $arrImg = (array) $img;
            $arrImg['alt'] = ['en' => 'alt'.$i];
            $arrOldImage[] = $arrImg;
            $i++;
        }

        $this->assertEquals($res22->data[0]->product_name->en, $this->testData['product_name']['en']);

        $newName = 'PHP7';
        $this->testData['product_name']['en'] = $newName;
        $newDesc = 'PHP7 - desc';
        $this->testData['product_description']['en'] = $newDesc;

        $imagesNew = $this->testData['images'][1];

        $imagesTab = array_merge($arrOldImage, [$imagesNew]);

        $this->testData['images'] = $imagesTab;

        $response33 = $this->put('api/products/'.$productId.'?token='.$this->token, $this->testData);
        $res33 = $response33->getData();

        $this->assertTrue($res33->success);

        $response222 = $this->get('api/products?token='.$this->token);
        $res222 = $response222->getData();
        $productId2 = $res222->data[0]->id;

        $this->assertEquals(count($res222->data), 1);
        $this->assertEquals($productId2, $productId);
        $this->assertEquals($res222->data[0]->product_name->en, $newName);
        $this->assertEquals($res222->data[0]->product_description->en, $newDesc);

        $this->assertEquals(count($res222->data[0]->images), 3);

        $this->assertEquals($res222->data[0]->images[0]->alt->en, 'alt1');
        $this->assertEquals($res222->data[0]->images[1]->alt->en, 'alt2');
        $this->assertEquals($res222->data[0]->images[2]->alt->en, null);

    }

    public function test_it_will_delete_page_that_belongs_product()
    {
        $this->setTestData();

        $res0 = $this->post('api/products?token='.$this->token, $this->testData);
        $res = $res0->getData();
        $this->assertTrue($res->success);

        $pages = Page::All()->toArray();
        $products = Product::All()->toArray();

        $this->assertEquals(1, count($pages));
        $this->assertEquals(1, count($products));
        $this->assertEquals(count($products), count($pages));

        $pageId = $pages[0]['id'];
        $this->assertEquals($products[0]['page_id'], $pageId);

        $response0 = $this->delete('api/pages/'.$pageId.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $pagesAfter = Page::All()->toArray();
        $productsAfter = Product::All()->toArray();

        $this->assertEquals(0, count($pagesAfter));
        $this->assertEquals(0, count($productsAfter));
        $this->assertEquals(count($products), count($pages));
    }

    public function test_it_will_delete_product_docs()
    {
        $this->setTestData();

        $res0 = $this->post('api/products?token='.$this->token, $this->testData);
        $res = $res0->getData();

        $response22 = $this->get('api/products?token='.$this->token);
        $res22 = $response22->getData();
        $productId = $res22->data[0]->id;

        $this->assertEquals(1, Content::query()->where('product_id', $productId)->count());
        $this->assertEquals(1, Translate::query()->where('product_id', $productId)->count());

        $testFile = public_path($res22->data[0]->images[0]->fs->medium);
        $this->assertFileExists($testFile);

        $this->assertEquals(count($res22->data), 1);

        $translateBefore = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->get()->toArray();
        $this->assertEquals(2, count($translateBefore));
        $this->assertEquals(self::STR_DESC_IMG1, $translateBefore[0]['value']);
        $this->assertEquals(null, $translateBefore[1]['value']);

        $response33 = $this->delete('api/products/'.$productId.'?token='.$this->token);
        $res33 = $response33->getData();

        $this->assertTrue($res33->success);

        $this->assertEquals(0, Content::query()->where('product_id', $productId)->count());
        $this->assertEquals(0, Translate::query()->where('product_id', $productId)->count());

        $translateAfter = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->get()->toArray();
        $this->assertEmpty($translateAfter);

        $response222 = $this->get('api/products?token='.$this->token);
        $res222 = $response222->getData();

        $this->assertEmpty(count($res222->data));

        $testFileDirname = pathinfo($testFile, PATHINFO_DIRNAME);
        $testFileDirnameDeep = pathinfo($testFileDirname, PATHINFO_DIRNAME);

        $this->assertFileDoesNotExist($testFileDirname);
        $this->assertFileDoesNotExist($testFileDirnameDeep);
        $this->assertFileDoesNotExist($testFile);
    }

    public function test_it_will_get_change_position_product_images_0()
    {
        $this->setTestData();
        $response0 = $this->post('api/products?token='.$this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $productId = $res0->data->productId;
        $this->assertNotEmpty($productId);

        $resProd = $this->get('api/products?token='.$this->token);
        $res2prod = $resProd->getData();
        $this->assertEquals($res2prod->data[0]->images[0]->name, 'phpunittest1.jpg');

        $response2 = $this->get('api/images/product/'.$productId.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $this->assertEquals($res2->data[0]->name, 'phpunittest1.jpg');
        $this->assertEquals($res2->data[0]->position, 1);

        $resSwap = $this->patch('api/images/position/up/'.$res2->data[0]->id.'?token='.$this->token);

        $response2Swap = $this->get('api/images/product/'.$productId.'?token='.$this->token);
        $res2Swap = $response2Swap->getData();
        $this->assertTrue($res2Swap->success);
        $this->assertEquals(count($res2Swap->data), 2);

        $this->assertEquals($res2Swap->data[0]->name, 'phpunittest2.jpg');
        $this->assertEquals($res2Swap->data[0]->position, 1);

        $resprod3 = $this->get('api/products?token='.$this->token);
        $res3prod = $resprod3->getData();
        $this->assertEquals($res3prod->data[0]->images[0]->name, 'phpunittest2.jpg');

        $resSwap2 = $this->patch('api/images/position/up/'.$res2->data[0]->id.'?token='.$this->token);

        $response3Swap = $this->get('api/images/product/'.$productId.'?token='.$this->token);
        $res3Swap = $response3Swap->getData();
        $this->assertTrue($res3Swap->success);
        $this->assertEquals(count($res3Swap->data), 2);

        $this->assertEquals($res3Swap->data[0]->name, 'phpunittest1.jpg');
        $this->assertEquals($res3Swap->data[0]->position, 1);

        $resprod4 = $this->get('api/products?token='.$this->token);
        $res4prod = $resprod4->getData();
        $this->assertEquals($res4prod->data[0]->images[0]->name, 'phpunittest1.jpg');

    }

    /**
     * I found bug in the change position image in product, therefore I create lots of tests data
     */
    public function test_it_will_get_change_position_product_images_for_lots_of_items()
    {
        $this->setTestData();

        // it must be 2 product in this test!!!
        $this->testData['sku'] = '11';
        $this->testData['product_name']['en'] = 'name11';
        $r0 = $this->post('api/products?token='.$this->token, $this->testData);
        $this->assertTrue($r0->getData()->success);
        $this->testData['sku'] = '22';
        $this->testData['product_name']['en'] = 'name22';
        $r1 = $this->post('api/products?token='.$this->token, $this->testData);
        $this->assertTrue($r1->getData()->success);
        $this->testData['sku'] = '33';
        $this->testData['product_name']['en'] = 'name33';
        $r1 = $this->post('api/products?token='.$this->token, $this->testData);
        $this->assertTrue($r1->getData()->success);

        $testPage =
        [
            'title' => ['en' => 'test unpublished'],
            'short_title' => ['en' => 'unpuplish'],
            'published' => 0,
            'type' => 'cms',
            'content' => ['en' => 'pppppppp'],
            'menu_id' => null,
        ];
        $p = (app(PageService::class))->wrapCreate($testPage);
        $testPage['title']['en'] = 'uniq1';
        $p = (app(PageService::class))->wrapCreate($testPage);
        $testPage['title']['en'] = 'uniq2';
        $p = (app(PageService::class))->wrapCreate($testPage);
        $testPage['title']['en'] = 'uniq3';
        $testPage['menu_id'] = $this->menuId;
        $p = (app(PageService::class))->wrapCreate($testPage);

        $altEnLastImage = 'last image';
        $testData = [
            'product_name' => ['en' => 'php3 aplikacje bazodanowe'],
            'sku' => 'AN/34534_xx',
            'price' => 123,
            'product_description' => ['en' => 'opis ksiazki'],
            // 'photo' => null,
            'page_id' => $this->pageId,
            'images' => [
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => self::STR_DESC_IMG1]],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1)],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => self::STR_DESC_IMG1]],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1)],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => self::STR_DESC_IMG1]],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1)],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => self::STR_DESC_IMG1]],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1)],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => self::STR_DESC_IMG1]],
                ['name' => $this->name2, 'data' => $this->getFixtureBase64($this->name2),  'alt' => ['en' => $altEnLastImage]],
            ],
        ];

        $response0 = $this->post('api/products?token='.$this->token, $testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->assertNotEmpty($res0->data->productId);

        $countImgs = count($testData['images']);
        $this->assertNotEmpty($countImgs);

        $countAllImages = Image::count();
        $allImgsBefore = Image::where('product_id', $res0->data->productId)->get()->toArray();

        $this->assertEquals($countImgs, count($allImgsBefore));

        $lastImage = $allImgsBefore[$countImgs - 1];

        $this->assertEquals($this->name2, $lastImage['name']);
        $this->assertEquals($countImgs, $lastImage['position']);
        $this->assertEquals($res0->data->productId, $lastImage['product_id']);
        $this->assertEquals(null, $lastImage['page_id']);

        // for($i = 0; $i < count($allImgsBefore)-1; $i++){
        $resSwap = $this->patch('api/images/position/up/'.$lastImage['id'].'?token='.$this->token);
        $res = $resSwap->getData();
        $this->assertTrue($res->success);

        $resSwap = $this->patch('api/images/position/up/'.$lastImage['id'].'?token='.$this->token);
        $res = $resSwap->getData();
        $this->assertTrue($res->success);

        $resSwap = $this->patch('api/images/position/up/'.$lastImage['id'].'?token='.$this->token);
        $res = $resSwap->getData();
        $this->assertTrue($res->success);

        $resSwap = $this->patch('api/images/position/down/'.$lastImage['id'].'?token='.$this->token);
        $res = $resSwap->getData();
        $this->assertTrue($res->success);

        $resSwap = $this->patch('api/images/position/down/'.$lastImage['id'].'?token='.$this->token);
        $res = $resSwap->getData();
        $this->assertTrue($res->success);

        $resSwap = $this->patch('api/images/position/down/'.$lastImage['id'].'?token='.$this->token);
        $res = $resSwap->getData();
        $this->assertTrue($res->success);
        // }

        $allImgsAfter = Image::where('product_id', $res0->data->productId)->orderBy('position')->get()->toArray();

        $ImageAfter = $allImgsAfter[$countImgs - 1];
        $this->assertEquals($this->name2, $ImageAfter['name']);
        $this->assertEquals($countImgs, $ImageAfter['position']);
        $this->assertEquals($res0->data->productId, $ImageAfter['product_id']);
        $this->assertEquals(null, $ImageAfter['page_id']);

        $resGet = $this->get('api/products?token='.$this->token);
        $resG = $resGet->getData();
        $this->assertTrue($resG->success);

    }

    private function createProduct($i = 1)
    {
        $d = $this->testData;
        $d['product_name']['en'] = $this->testData['product_name']['en'].'_'.$i;
        $d['sku'] = $this->testData['sku'].'_'.$i;
        $d['price'] = 100 + $i;
        $d['product_description']['en'] = $this->testData['product_description']['en'].'_'.$i;

        $res0 = $this->post('api/products?token='.$this->token, $d);
        $res = $res0->getData();
        $this->assertTrue($res->success);
        $this->assertNotEmpty($res->data->productId);

        return $res->data; // ->productId;
    }

    private function createProduct2($i = 1)
    {
        $d = $this->testData2;
        $d['product_name']['en'] = $this->testData2['product_name']['en'].'_'.$i;
        $d['sku'] = $this->testData2['sku'].'_'.$i;
        $d['price'] = 200 + $i;
        $d['product_description']['en'] = $this->testData2['product_description']['en'].'_'.$i;
        $d['published'] = 0;

        $res0 = $this->post('api/products?token='.$this->token, $d);
        $res = $res0->getData();
        $this->assertTrue($res->success);
        $this->assertNotEmpty($res->data->productId);

        return $res->data; // ->productId;
    }

    private function createProducts($nu)
    {
        for ($i = 1; $i <= $nu; $i++) {
            $this->createProduct($i);
        }
    }

    /**
     * get one product
     */
    public function test_get_product_by_given_id_docs()
    {
        $this->setTestData();
        $d = $this->createProduct();

        $response = $this->get('api/products/'.$d->productId.'?token='.$this->token);
        $res = $response->getData();

        $this->assertTrue($res->success);
        $this->assertEquals($d->productId, $res->data->id);

        $this->assertNotEmpty($res->data->price);
        $this->assertNotEmpty($res->data->price_description);

        $this->assertEquals($this->testData['page_id'], $res->data->page_id);
    }

    /**
     * get one product additional check
     */
    public function test_get_product_by_given_id_check_many_products()
    {
        $this->setTestData();
        $this->setTestData2();

        $product1a = $this->createProduct(1);
        $product1b = $this->createProduct(2);
        $product2 = $this->createProduct2();

        // 1
        $response = $this->get('api/products/'.$product1a->productId.'?token='.$this->token);
        $res = $response->getData();

        $this->assertTrue($res->success);
        $this->assertEquals($product1a->productId, $res->data->id);

        // 2
        $response2 = $this->get('api/products/'.$product1b->productId.'?token='.$this->token);
        $res2 = $response2->getData();

        $this->assertTrue($res2->success);
        $this->assertEquals($product1b->productId, $res2->data->id);

        // 3
        $response3 = $this->get('api/products/'.$product2->productId.'?token='.$this->token);
        $res3 = $response3->getData();

        $this->assertTrue($res3->success);
        $this->assertEquals($product2->productId, $res3->data->id);
    }

    private function setFakeData()
    {
        $this->setTestData();
        $numbersOfProducts = 32;
        $this->createProducts($numbersOfProducts);

        $this->setTestData2();
        $this->createProduct2();
    }

    /**
     * pagination products
     */
    public function test_it_will_get_many_products_with_pagination()
    {
        $this->setFakeData();
        $lang = 'en';

        /* product_name test */
        $column = 'product_name';
        $direction = 'desc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(1, $res->data->current_page);

        $firstEl = reset($res->data->data);
        $this->assertEquals('php3 db app_9', $firstEl->product_name);

        /* page_short_title test */
        $column = 'page_short_title';
        $direction = 'desc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(1, $res->data->current_page);

        $firstEl = reset($res->data->data);
        $this->assertEquals('page2', $firstEl->page_short_title);

        /* id test */
        $column = 'id';
        $direction = 'desc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(1, $res->data->current_page);

        $firstEl = reset($res->data->data);
        $this->assertEquals(Product::All()->last()->id, $firstEl->id);

        /* published test */
        $column = 'published';
        $direction = 'asc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(1, $res->data->current_page);

        $firstEl = reset($res->data->data);
        $this->assertEquals('page2', $firstEl->page_short_title);
        $this->assertEquals('0', $firstEl->published);

        /* sku test */
        $column = 'sku';
        $direction = 'desc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(1, $res->data->current_page);

        $firstEl = reset($res->data->data);
        $this->assertEquals('AN/34534_9', $firstEl->sku);

        /* price test */
        $column = 'price';
        $direction = 'desc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(1, $res->data->current_page);

        $firstEl = reset($res->data->data);
        $this->assertEquals('page2', $firstEl->page_short_title);
        $this->assertEquals('201', $firstEl->price);
        $this->assertEquals('$2.01', $firstEl->price_description);

    }

    public function test_it_will_get_next_pagination_page()
    {
        $this->setFakeData();
        $lang = 'en';
        /* id test */
        $column = 'id';
        $direction = 'desc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertNotEmpty($res->data->data[0]);

        $this->assertEquals(1, $res->data->current_page);

        $firstEl = reset($res->data->data);

        $id1 = Product::All()->last()->id;
        $this->assertEquals($id1, $firstEl->id);

        $response2 = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?page=2&token='.$this->token);
        $res2 = $response2->getData();

        $this->assertNotEmpty($res2->data->data[0]);

        $this->assertEquals(2, $res2->data->current_page);

        // These tests are useless.
        // $firstEl2 = reset($res2->data->data);
        // $pagination = getenv('PAGINATION');
        // dump($pagination);
        // dump( $id1-$pagination, $firstEl2->id );
        // $this->assertEquals( $id1-$pagination, $firstEl2->id );
    }

    public function test_it_will_get_second_pagination_page_check_index()
    {
        $this->setFakeData();
        $lang = 'en';
        $column = 'id';
        $direction = 'desc';

        $response2 = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?page=2&token='.$this->token);
        $res2 = $response2->getData();

        $this->assertNotEmpty($res2->data->data[0]);
    }

    public function test_restrict_products_sorted_columns_to_specific_names()
    {
        $this->setTestData();
        $numbersOfProducts = 1;
        $this->createProducts($numbersOfProducts);

        $lang = 'en';
        $column = 'fake';
        $direction = 'desc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $objProduct = new Product;
        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'available columns to sort products: '.implode(',', $objProduct->columnsAllowedToSort),
        ]);
    }

    public function test_restrict_products_sorted_direction_to_specific_names()
    {
        $lang = 'en';
        $column = 'id';
        $direction = 'fake';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'available direction to sort: '.implode(',', SortDirection::values()),
        ]);
    }

    public function test_restrict_products_sorted_to_specific_langs()
    {
        $lang = 'enn_fake';
        $column = 'id';
        $direction = 'asc';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'wrong lang in url',
        ]);
    }

    public function test_sort_products_by_sku()
    {
        $this->setTestData();
        $this->setTestData2();

        $product1a = $this->createProduct(1);
        $product1b = $this->createProduct(2);
        $product2 = $this->createProduct2();

        $lang = 'en';
        $column = 'sku';

        $sku1a = $product1a->data->sku;
        $sku1b = $product1b->data->sku;
        $sku2 = $product2->data->sku;

        $direction = 'asc';
        $response0 = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res0 = $response0->getData();
        $this->assertEquals($res0->data->data[0]->sku, 'AN/34534_1');
        $this->assertEquals($res0->data->data[1]->sku, 'AN/34534_2');
        $this->assertEquals($res0->data->data[2]->sku, 'AN/34534_22_1');

        $direction = 'desc';
        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res = $response->getData();

        $this->assertEquals($res->data->data[0]->sku, 'AN/34534_22_1');
        $this->assertEquals($res->data->data[1]->sku, 'AN/34534_2');
        $this->assertEquals($res->data->data[2]->sku, 'AN/34534_1');
    }

    public function test_sort_products_by_name()
    {
        $this->setTestData();
        $this->setTestData2();

        $product1a = $this->createProduct(1);
        $product1b = $this->createProduct(2);
        $product2 = $this->createProduct2();

        $lang = 'en';
        $column = 'product_name';

        $direction = 'asc';
        $response0 = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res0 = $response0->getData();

        $this->assertEquals($res0->data->data[0]->product_name, 'php3 db app 2_1');
        $this->assertEquals($res0->data->data[1]->product_name, 'php3 db app_1');
        $this->assertEquals($res0->data->data[2]->product_name, 'php3 db app_2');

        $direction = 'desc';
        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res = $response->getData();

        $this->assertEquals($res->data->data[0]->product_name, 'php3 db app_2');
        $this->assertEquals($res->data->data[1]->product_name, 'php3 db app_1');
        $this->assertEquals($res->data->data[2]->product_name, 'php3 db app 2_1');
    }

    public function test_sort_products_by_price()
    {
        $this->setTestData();
        $this->setTestData2();

        $product1a = $this->createProduct(1);
        $product1b = $this->createProduct(2);
        $product2 = $this->createProduct2();

        $lang = 'en';
        $column = 'price';

        $direction = 'asc';
        $response0 = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res0 = $response0->getData();

        $this->assertEquals($res0->data->data[0]->price, 101);
        $this->assertEquals($res0->data->data[0]->price_description, '$1.01');

        $this->assertEquals($res0->data->data[1]->price, 102);
        $this->assertEquals($res0->data->data[1]->price_description, '$1.02');

        $this->assertEquals($res0->data->data[2]->price, 201);
        $this->assertEquals($res0->data->data[2]->price_description, '$2.01');

        $direction = 'desc';
        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res = $response->getData();

        $this->assertEquals($res->data->data[0]->price, 201);
        $this->assertEquals($res->data->data[1]->price, 102);
        $this->assertEquals($res->data->data[2]->price, 101);
    }

    public function test_sort_products_by_published()
    {
        $this->setTestData();
        $this->setTestData2();

        $product1a = $this->createProduct(1);
        $product1b = $this->createProduct(2);
        $product2 = $this->createProduct2();

        $lang = 'en';
        $column = 'published';

        $direction = 'asc';
        $response0 = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res0 = $response0->getData();

        $this->assertEquals($res0->data->data[0]->published, 0);

        $direction = 'desc';
        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);

        $res = $response->getData();

        $this->assertEquals($res->data->data[0]->published, 1);
    }

    public function test_search_products_by_sku()
    {
        $this->setTestData();
        $this->setTestData2();

        $product1a = $this->createProduct(1);
        $product1b = $this->createProduct(2);
        $product2 = $this->createProduct2();

        $sku1a = $product1a->data->sku;
        $sku1b = $product1b->data->sku;
        $sku2 = $product2->data->sku;

        $lang = 'en';
        $column = 'sku';
        $direction = 'asc';

        $skuSearch = 'AN/34534_2';

        $response = $this->get('api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token.'&search='.$skuSearch);

        $res = $response->getData();

        $this->assertTrue($res->success);

        $dd = $res->data->data;
        $this->assertEquals(2, count($dd));
        $this->assertEquals($sku1b, $dd[0]->sku);
        $this->assertEquals($sku2, $dd[1]->sku);
    }

    public function test_search_products_by_many_columns_pagination_docs()
    {
        $this->setTestData();
        $d['sku'] = 'app_1';
        $this->setTestData2($d);

        $product1a = $this->createProduct(1);
        $product1b = $this->createProduct(2);
        $product2 = $this->createProduct2();

        // $sku1a = $product1a->data->sku;
        // $sku1b = $product1b->data->sku;
        $sku2 = $product2->data->sku;

        $pName1a = $product1a->data->product_name->en;
        // $pName1b = $product1b->data->product_name->en;
        // $pName2 = $product2->data->product_name->en;

        $this->assertEquals('app_1_1', $sku2);
        $this->assertEquals('php3 db app_1', $pName1a);

        $lang = 'en';
        $column = 'sku';
        $direction = 'asc';

        $search = 'app_1';

        $url = 'api/products/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token.'&search='.$search;
        $response = $this->get($url);

        $res = $response->getData();

        $this->assertTrue($res->success);
        $dd = $res->data->data;
        $this->assertEquals(2, count($dd));
        $this->assertNotEmpty($dd[0]->id);
        $this->assertNotEmpty($dd[0]->sku);
        $this->assertNotEmpty($dd[0]->price);
        $this->assertNotEmpty($dd[0]->price_description);
        $this->assertNotEmpty($dd[0]->published);
        $this->assertNotEmpty($dd[0]->page_id);
        $this->assertNotEmpty($dd[0]->created_at);
        $this->assertNotEmpty($dd[0]->updated_at);
        $this->assertNotEmpty($dd[0]->product_name);
        $this->assertNotEmpty($dd[0]->page_short_title);
        $this->assertNotEmpty($dd[0]->images);
        $this->assertTrue(is_array($dd[0]->images));

    }

    /**
     * checkouts - pagination
     */
    public function test_restrict_checkouts_sorted_columns_to_specific_names()
    {
        $this->warpSaveTestCheckoutManyTimes(2);

        $lang = 'en';
        $column = 'fake';
        $direction = 'desc';

        $response = $this->get('api/checkouts/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $objCheckout = new Checkout;
        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'available columns to sort checkouts: '.implode(',', $objCheckout->columnsAllowedToSort),
        ]);
    }

    public function test_restrict_checkouts_sorted_direction_to_specific_names()
    {
        $lang = 'en';
        $column = 'id';
        $direction = 'fake';

        $response = $this->get('api/checkouts/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'available direction to sort: '.implode(',', SortDirection::values()),
        ]);
    }

    public function test_restrict_checkouts_sorted_to_specific_langs()
    {
        $lang = 'enn_fake';
        $column = 'id';
        $direction = 'asc';

        $response = $this->get('api/checkouts/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token);
        $res = $response->getData();

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'wrong lang in url',
        ]);
    }

    public function test_sort_checkout_by_total_price()
    {
        $times = 32;
        $out = $this->warpSaveTestCheckoutManyTimes($times);
        $this->assertEquals($times, count($out));

        $lang = 'en';
        $column = 'price_total_add_deliver';
        $direction = 'asc';
        $search = '';

        $url = 'api/checkouts/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token.'&search='.$search;
        $response = $this->get($url);
        $res = $response->getData();

        $this->assertNotEmpty($res->data->data[0]->price_total);
        $this->assertNotEmpty($res->data->data[0]->price_deliver);
        $this->assertNotEmpty($res->data->data[0]->price_total_add_deliver);

        $this->assertStringContainsString('$', $res->data->data[0]->price_total_description);
        $this->assertStringContainsString('$', $res->data->data[0]->price_deliver_description);
        $this->assertStringContainsString('$', $res->data->data[0]->price_total_add_deliver_description);

        $this->assertNotEmpty($res->data->data[1]->price_total_add_deliver);
        $this->assertNotEmpty($res->data->data[2]->price_total_add_deliver);
        $this->assertNotEmpty($res->data->data[3]->price_total_add_deliver);

        $this->assertTrue($res->data->data[0]->price_total_add_deliver < $res->data->data[1]->price_total_add_deliver);
        $this->assertTrue($res->data->data[1]->price_total_add_deliver < $res->data->data[2]->price_total_add_deliver);
        $this->assertTrue($res->data->data[2]->price_total_add_deliver < $res->data->data[3]->price_total_add_deliver);

        $this->simpleTestCheckoutData($res->data->data[0]);
    }

    public function test_search_and_sort_checkout_by_total_price_docs()
    {
        $times = 31;
        $out = $this->warpSaveTestCheckoutManyTimes($times);
        $this->assertEquals($times, count($out));

        $lang = 'en';
        $column = 'price_total_add_deliver';
        $direction = 'asc';
        $search = 'client3';

        $url = 'api/checkouts/pagination/'.$lang.'/'.$column.'/'.$direction.'?token='.$this->token.'&search='.$search;
        $response = $this->get($url);
        $res = $response->getData();

        $this->assertEquals(2, count($res->data->data)); // client3, client30
        $this->simpleTestCheckoutData($res->data->data[0]);
    }

    public function test_front_search_fake_products()
    {
        $response = $this->get('/search?key=abc');
        $response->assertStatus(200);
    }

    public function test_front_search_fake_with_wrong_lang_products()
    {
        $response = $this->get('/enn_fake/search?key=abc');
        $response->assertStatus(404);
    }

    public function test_front_search_fake_with_correct_lang_products()
    {
        $response = $this->get('/en/search?key=abc');
        $response->assertStatus(404); // because in one lang we can't add lang in url
    }

    public function test_front_search_real_products()
    {
        $this->setTestData();
        $product_name = self::STR_PRODUCT_NAME_EN;
        $response = $this->get('/search?key='.$product_name);
        $response->assertStatus(200);
    }

    public function test_front_search_all_products()
    {
        $response = $this->get('/search?key=');
        $response->assertStatus(200);
    }

    public function test_front_checkout()
    {
        $response = $this->get('/checkout');
        $response->assertStatus(200);
    }

    /**
     * OLD TESTS
     */

    /**
     * it is not test admin
     */
    /*
    public function test_it_will_save_to_basket()
    {
        $ids = $this->setAddTwoProducts();
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        $user = Auth::user();

        $baskets = [
            0 => [
              "qty" => 10,
              "user_id" => $user->id,
              "product_id" => $id1
            ],
            1 => [
              "qty" => 5,
              "user_id" => $user->id,
              "product_id" => $id2
            ]
        ];

        $objBaskets = Basket::inBasketByUserId($user->id);
        $this->assertEmpty($objBaskets);

        Basket::deleteBasketByObjBaskets($objBaskets);

        Basket::saveBaskets($baskets);
        $objBaskets2 = Basket::inBasketByUserId($user->id);
        $this->assertNotEmpty($objBaskets2);

        Basket::deleteBasketByObjBaskets($objBaskets2);

        $objBaskets3 = Basket::inBasketByUserId($user->id);
        $this->assertEmpty($objBaskets3);

        $isNewOrders = Order::moveDataFromBasketToOrderForUser();
        $this->assertFalse($isNewOrders);
        $objOrders = Order::inOrdersByUserId($user->id);
        $this->assertEmpty($objOrders);
        //dd($basket);
    }
    */

    /**
     * it is not test admin
     */
    /*
    public function test_it_will_save_to_order()
    {
        $ids = $this->setAddTwoProducts();
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        $user = Auth::user();

        $baskets = [
            0 => [
              "qty" => 10,
              "user_id" => $user->id,
              "product_id" => $id1
            ],
            1 => [
              "qty" => 5,
              "user_id" => $user->id,
              "product_id" => $id2
            ]
        ];

        Basket::deleteBasketAndAddNewData($user->id, $baskets);
        $objBaskets4 = Basket::inBasketByUserId($user->id);
        $this->assertNotEmpty($objBaskets4);

        $isNewOrders2 = Order::moveDataFromBasketToOrderForUser();
        $this->assertTrue($isNewOrders2);

        $objBaskets5 = Basket::inBasketByUserId($user->id);
        $this->assertEmpty($objBaskets5);

        $objOrders2 = Order::inOrdersByUserId($user->id);
        $this->assertNotEmpty($objOrders2);

        $b = $objBaskets4->toArray();
        $o = $objOrders2->toArray();

        $count = 2;
        $this->assertEquals($count, count($b));
        $this->assertEquals($count, count($o));

        for($i =0; $i<$count; $i++){
            $this->assertEquals($b[$i]["qty"], $o[$i]["qty"]);
            $this->assertEquals($b[$i]["user_id"], $o[$i]["user_id"]);
            $this->assertEquals($b[$i]["product_id"], $o[$i]["product_id"]);
        }
    }
    */

    /**
     * it is not test admin
     */
    /*
    public function test_it_will_save_two_times_to_order()
    {
        $ids = $this->setAddTwoProducts();
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];

        $user = Auth::user();

        $qty0a = 10;
        $baskets = [
            0 => [
              "qty" => $qty0a,
              "user_id" => $user->id,
              "product_id" => $id1
            ],
            // 1 => [
            //   "qty" => 5,
            //   "user_id" => $user->id,
            //   "product_id" => $id2
            // ]
        ];

        //first time
        Basket::deleteBasketAndAddNewData($user->id, $baskets);
        $objBaskets4 = Basket::inBasketByUserId($user->id);
        $this->assertNotEmpty($objBaskets4);

        $isNewOrders2 = Order::moveDataFromBasketToOrderForUser();
        $this->assertTrue($isNewOrders2);

        $objBaskets5 = Basket::inBasketByUserId($user->id);
        $this->assertEmpty($objBaskets5);

        $objOrders2 = Order::inOrdersByUserId($user->id);
        $this->assertNotEmpty($objOrders2);

        $b = $objBaskets4->toArray();
        $o = $objOrders2->toArray();

        $count = 1;
        $this->assertEquals($count, count($b));
        $this->assertEquals($count, count($o));

        for($i =0; $i<$count; $i++){
            $this->assertEquals($b[$i]["qty"], $o[$i]["qty"]);
            $this->assertEquals($b[$i]["user_id"], $o[$i]["user_id"]);
            $this->assertEquals($b[$i]["product_id"], $o[$i]["product_id"]);
        }

        //second time
        $qty0b = 22;
        $qty1 = 7;
        $baskets[0]['qty'] = $qty0b;
        $baskets[1] = [
              "qty" => $qty1,
              "user_id" => $user->id,
              "product_id" => $id2
        ];

        Basket::deleteBasketAndAddNewData($user->id, $baskets);
        $objBaskets4 = Basket::inBasketByUserId($user->id);
        $this->assertNotEmpty($objBaskets4);

        $isNewOrders2 = Order::moveDataFromBasketToOrderForUser();
        $this->assertTrue($isNewOrders2);

        $objBaskets5 = Basket::inBasketByUserId($user->id);
        $this->assertEmpty($objBaskets5);

        $objOrders2 = Order::inOrdersByUserId($user->id);
        $this->assertNotEmpty($objOrders2);

        $b2 = $objBaskets4->toArray();
        $o2 = $objOrders2->toArray();

        $count = 2;
        $this->assertEquals($count, count($b2));
        $this->assertEquals($count, count($o2));

        $this->assertEquals($qty0a + $qty0b, $o2[0]["qty"]);
        $this->assertEquals($user->id, $o2[0]["user_id"]);
        $this->assertEquals($id1, $o2[0]["product_id"]);

        $this->assertEquals($qty1, $o2[1]["qty"]);
        $this->assertEquals($user->id, $o2[1]["user_id"]);
        $this->assertEquals($id2, $o2[1]["product_id"]);
    }
    */

    /**
     * it is not test admin
     * it tests home/api/tobank
     */
    /*
    public function test_it_will_post_to_bank()
    {
        //dd($products);

        $ids = $this->setAddTwoProducts();
        $id1 = $ids['id1'];
        $id2 = $ids['id2'];


        //name and price is not important in this post
        $json =
        '{
            "cart": [
                {
                    "id": '.$id1.',
                    "name": "PHP3 aplikacje bazodanowe - xxxxxxxxxxxxxxxxxxxxxxxyyyyyyyyyyyyyy",
                    "price": 119999999999999999999,
                    "qty": 10
                },
                {
                    "id": '.$id2.',
                    "name": "PHP5 nott imporstentttttttttttt",
                    "price": 30999999999999,
                    "qty": 5
                }
            ]
        }';

        $obj = json_decode($json);
        $this->assertEquals(2, count($obj->cart));


        $this->assertAuthenticated();
        $token = User::getTokenForClient();
        $this->assertNotEmpty($token);

        $user = Auth::user();
        $this->assertTrue(Auth::check()); //I dont understand why becayse we dont use this: //Auth::login($user);

        $response = $this->post('home/api/tobank?token='.$token, ["cart" => $obj->cart] );
        $response->assertStatus(200);
        //dd( $response->getData() );

        $res1 = $response->getData();
        $this->assertTrue($res1->success);
        $this->assertNotEmpty($res1->data);
        //dd($res1->data);

        $user = Auth::user();
        $objBaskets4 = Basket::inBasketByUserId($user->id);
        $this->assertNotEmpty($objBaskets4);

        $baskets4 = $objBaskets4->toArray();

        //dump($baskets4);
        //dump($obj->cart);
        //dd('____00__');

        $i = 0;
        foreach($obj->cart as $cart ){
            $this->assertEquals($baskets4[$i]['product_id'], $cart->id  );
            $this->assertEquals($baskets4[$i]['qty'], $cart->qty  );
            $i++;
        }

    }
    */

    /*
    public function test_it_will_get_product_data_by_product_id()
    {
        $this->setTestData();

        $response0 = $this->post('api/products?token=' . $this->token, $this->testData);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $productId = $res0->data->productId;
        $this->assertNotEmpty($productId);

        $data = (new Product)->getProductDataByProductId( $productId );
        $this->assertEquals($productId, $data['id']);
        $this->assertEquals(self::STR_PRODUCT_NAME_EN, $data['product_name']['en']);

        $urlCategory = $data['url_category']['en'];
        $urlProduct = $data['url_product']['en'];

        $response = $this->get($urlCategory);
        $response->assertStatus(200);

        $response = $this->get($urlProduct);
        $response->assertStatus(200);

        //$pos = strpos($response->getContent(), $pageTitle);
        //$this->assertNotEmpty($pos, $pageTitle);


        $this->testData['product_name']['en'] = 'test uniq';
        $this->testData['sku'] = 'AN/34534_qwe';
        $this->testData['page_id'] = strval($this->testData['page_id']);

        var_dump($this->testData['page_id']);
        $response1 = $this->post('api/products?token=' . $this->token, $this->testData);
        $res1 = $response1->getData();
        $this->assertTrue($res1->success);
    }
    */

}
