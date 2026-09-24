<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\Cms\Page\PageService;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\RequestService;
use App\Services\Cmsrs\Navigation\UrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;

// use Illuminate\Support\Facades\Cookie;

// use Tests\TestCase;

class ConfigTest extends Base
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        putenv('LANGS="en"');
        putenv('API_SECRET=""');
        putenv('CURRENCY="EUR"');
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
        (new ConfigService)->deleteFileCacheEnableIfExist();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_it_will_get_config_docs()
    {
        $response = $this->get('api/config?token='.$this->token);
        $res = $response->getData();
        // print_r($res);

        $this->assertTrue($res->success);
        $this->assertNotEmpty($res->data);

        /***************/
        /**page_types **/
        /***************/
        $this->assertNotEmpty($res->data->page_types);
        $this->assertTrue(is_array($res->data->page_types));
        $this->assertEquals('privacy_policy', $res->data->page_types[5]);

        $in = false;
        foreach ($res->data->page_types as $page_type) {
            $data = [
                'title' => ['en' => $page_type],
                'short_title' => ['en' => $page_type],
                'description' => ['en' => 'Description... Needed for google'],
                'published' => 1,
                'commented' => 1,
                'type' => $page_type,
                'content' => ['en' => 'test'],
                'menu_id' => null,
            ];

            $p = (app(PageService::class))->wrapCreate($data);
            // dump($p->toArray());

            $in = true;

            $url = (app(UrlService::class))->getUrl($p, 'en');
            if (! $url) { // inner type
                $this->assertEquals('inner', $data['type']);

                continue;
            }

            $response = $this->get($url);

            // $status = (($page_type === 'login') || ($page_type === 'register') || ($page_type === 'forgot')) ? 302 : 200; //I don't understand - todo (why register and forgot??)
            $status = 200;
            // if ($page_type == 'shoppingsuccess') {
            //     $status = 404;
            // }

            $response->assertStatus($status);
        }
        $this->assertTrue($in);
        $pagesNum = Page::all()->count();
        $this->assertEquals($pagesNum, count($res->data->page_types));

        /***************/
        /*******langs **/
        /***************/
        $this->assertTrue(is_array($res->data->langs));
        $this->assertEquals(1, count($res->data->langs));
        $this->assertEquals('en', ($res->data->langs[0]));

        /***************/
        /**default_lang*/
        /***************/
        $this->assertEquals('en', $res->data->default_lang);

        /***************/
        /*******cache **/
        /***************/
        $this->assertEquals(true, $res->data->cache_enable);
        $this->assertEquals(false, $res->data->is_cache_enable);

        /***************/
        /*** currency **/
        /***************/
        $this->assertEquals('EUR', $res->data->currency);

        /***************/
        /*** is shop **/
        /***************/
        $this->assertEquals(true, $res->data->is_shop);

        /***************/
        /* demo status */
        /***************/
        $this->assertEquals(false, $res->data->demo_status);
    }

    public function test_it_will_get_exception_no_langs()
    {
        $config = (new ConfigService);

        // $langs = $config->getLangs();
        // $this->assertEquals("en", $langs);

        $langs = $config->getLangsFromEnv();
        $this->assertEquals('en', $langs);

        $config->setLangs('');
        // $this->expectException(\Exception::class);
        $langs = $config->getLangsFromEnv();
        $this->assertEquals(ConfigService::LANG_DEFAULT, $langs);
    }

    public function test_it_will_clear_cache_docs()
    {
        $response = $this->put('api/config/clearcache?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
    }

    public function test_it_will_create_sitemap_docs()
    {
        $response = $this->put('api/config/createsitemap?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
    }

    public function test_get_test_cache_enable_file()
    {
        $filePath = (new ConfigService)->getCacheEnableFilePath();
        $expectedSuffix = 'storage/app/cache_enable_test.txt';
        $this->assertStringEndsWith($expectedSuffix, $filePath);
    }

    public function test_it_will_create_cache_enable_file()
    {
        $createFile = (new ConfigService)->createFileCacheEnableIfNotExist();
        $this->assertTrue($createFile);
    }

    public function test_api_it_will_create_cache_enable_file_two_times_docs()
    {
        $post = ['action' => 'enable'];
        $response = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post);
        $response->assertStatus(200);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertTrue((new ConfigService)->isExistCacheFileEnable());
        $this->assertEquals('Cache enabled', $res->data->message);
        $this->assertEquals(true, $res->data->value);

        $response2 = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post);
        $res2 = $response2->getData();
        $this->assertFalse($res2->success);
        $this->assertTrue((new ConfigService)->isExistCacheFileEnable());
        $this->assertEquals('Cache was already enabled', $res2->error->toggle_cache_enable_file);
    }

    public function test_it_will_create_cache_enable_file_and_delete()
    {
        $createFile = (new ConfigService)->createFileCacheEnableIfNotExist();
        $this->assertTrue((new ConfigService)->isExistCacheFileEnable());
        $this->assertTrue($createFile);

        $deleteFile = (new ConfigService)->deleteFileCacheEnableIfExist();
        $this->assertTrue($deleteFile);
        $this->assertTrue(! (new ConfigService)->isExistCacheFileEnable());
    }

    public function test_api_it_will_create_cache_enable_file_and_delete_two_times_docs()
    {
        $post = ['action' => 'enable'];
        $response = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertTrue((new ConfigService)->isExistCacheFileEnable());
        $this->assertEquals('Cache enabled', $res->data->message);
        $this->assertEquals(true, $res->data->value);

        $post2 = ['action' => 'disable'];
        $response2 = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post2);
        $response2->assertStatus(200);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertTrue(! (new ConfigService)->isExistCacheFileEnable());
        $this->assertEquals('Cache disabled', $res2->data->message);
        $this->assertEquals(false, $res2->data->value);

        $post3 = ['action' => 'disable'];
        $response3 = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post3);
        $res3 = $response3->getData();
        $this->assertFalse($res3->success);
        $this->assertTrue(! (new ConfigService)->isExistCacheFileEnable());
        $this->assertEquals('Cache was already disabled', $res3->error->toggle_cache_enable_file);
    }

    public function test_api_toggle_cache_enable_file_with_fake_param()
    {
        $post = ['action' => 'fake'];
        $response = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post);
        $response->assertStatus(400);
        $res = $response->getData();
        $this->assertEquals('Invalid action', $res->error->toggle_cache_enable_file);
    }

    public function test_api_toggle_cache_enable_file_with_wrong_post()
    {
        $post = ['test' => 'fake'];
        $response = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post);
        $response->assertStatus(400);
        $res = $response->getData();
        $this->assertEquals('Invalid action', $res->error->toggle_cache_enable_file);
    }

    /**
     * see UserTest the test is the same, but result is different because here is CACHE_ENABLE="true"
     */
    public function test_check_is_cache_enable_for_cache_enable_true()
    {
        $this->assertFalse((new ConfigService)->isExistCacheFileEnable()); // see setup
        $isCache = (new ConfigService)->isCacheEnable();
        $this->assertFalse($isCache);
    }

    /**
     * CACHE_ENABLE=true
     */
    public function test_is_cache_enable_it_is_only_one_case_when_cache_is_enable()
    {
        $this->assertFalse((new ConfigService)->isExistCacheFileEnable()); // see setup
        $post = ['action' => 'enable'];
        $response = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post);
        $response->assertStatus(200);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertTrue((new ConfigService)->isExistCacheFileEnable());

        $isCache = (new ConfigService)->isCacheEnable();
        $this->assertTrue($isCache);
    }

    public function test_api_is_cache_enable_false_docs()
    {
        $this->assertFalse((new ConfigService)->isExistCacheFileEnable()); // see setup
        $response = $this->get('api/config/is-cache-enable?token='.$this->token);
        $response->assertStatus(200);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertFalse($res->data->is_cache_enable);
    }

    public function test_api_is_cache_enable_true()
    {
        $post = ['action' => 'enable'];
        $response = $this->post('api/config/toggle-cache-enable-file?token='.$this->token, $post);
        $response->assertStatus(200);
        $this->assertTrue((new ConfigService)->isExistCacheFileEnable());

        $response = $this->get('api/config/is-cache-enable?token='.$this->token);
        $response->assertStatus(200);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertTrue($res->data->is_cache_enable);
    }

    public function test_get_lang_from_cookie()
    {
        $defaultLang = (new ConfigService)->getDefaultLang();
        $this->assertEquals('en', $defaultLang);

        $langFromCookie = app(RequestService::class)->getLangFromCookie();
        $this->assertEquals($defaultLang, $langFromCookie);
    }
}
