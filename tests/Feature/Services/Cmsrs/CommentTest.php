<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Menu;
use App\Services\Cmsrs\Cms\Page\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends Base
{
    use RefreshDatabase;

    private $name1;

    private $name2;

    private $testData;

    private $testPage;

    private $testMenu;

    private $menuId;

    private $pageId;

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
                'name' => 'books',
                'position' => 77,
            ];

        $menu = new Menu($this->testMenu);
        $save = $menu->save();
        $this->assertTrue($save);

        $menuObj = $menu->all()->first();

        $this->menuId = $menuObj->id;

        $type = 'cms';

        $this->testPage =
        [
            'title' => ['en' => 'programmer'],
            'short_title' => ['en' => 'page1'],
            'published' => 1,
            'commented' => 1,
            'position' => 7,
            'type' => $type,
            'content' => ['en' => 'content test133445'],
            'menu_id' => $this->menuId,
        ];

        $objPage = (app(PageService::class))->wrapCreate($this->testPage);

        $res = $this->get('api/pages/type/'.$type.'?token='.$this->token);

        $data = $res->getData();
        $this->pageId = $data->data[0]->id;

        $this->assertEquals($this->pageId, $objPage->id);
        $this->assertNotEmpty($this->pageId);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_it_will_deny_create_comment_fake_page()
    {
        $content = [
            'content' => 'test comment - test123 - fake page',
        ];

        $response = $this->post('api/comments/'. 134, $content);
        $response->assertStatus(404);
    }

    public function test_it_will_deny_create_comment_page_not_commented()
    {
        $content = [
            'content' => 'test comment - test123 - deny',
        ];

        $this->testPage['commented'] = 0;
        $objPage = (app(PageService::class))->wrapCreate($this->testPage);
        $this->assertNotEmpty($objPage->id);

        $response = $this->post('api/comments/'.$objPage->id, $content);
        $response->assertStatus(404);
    }

    public function test_it_will_create_comment_docs()
    {
        $content = [
            'content' => 'test comment - test123',
        ];

        $response = $this->post('api/comments/'.$this->pageId, $content);

        $res = $response->getData();
        $this->assertTrue($res->success);
    }

    public function test_it_will_get_comment_docs()
    {
        $content1 = [
            'content' => '111 test comment - test123',
        ];

        $res1 = $this->post('api/comments/'.$this->pageId, $content1);
        $r1 = $res1->getData();
        $this->assertTrue($r1->success);

        $content2 = [
            'content' => '222 test comment - test123',
        ];

        $res2 = $this->post('api/comments/'.$this->pageId, $content2);
        $r2 = $res2->getData();
        $this->assertTrue($r2->success);

        $response = $this->get('api/comments/'.$this->pageId);
        $res = $response->getData();

        $this->assertTrue($res->success);
        $this->assertTrue(is_array($res->data));

        $this->assertEquals(2, count($res->data));
        $this->assertEquals($content2['content'], $res->data[1]->content);
    }

    public function test_it_will_get_comment_and_return_404_page_not_found()
    {
        $content1 = [
            'content' => '111 test comment - test123',
        ];

        $res1 = $this->post('api/comments/'.$this->pageId, $content1);
        $r1 = $res1->getData();
        $this->assertTrue($r1->success);

        $response = $this->get('api/comments/'.($this->pageId + 1000));
        $response->assertStatus(404);
    }

    public function test_it_will_get_comment_and_return_404_comment_not_found()
    {
        $this->testPage['commented'] = 0;
        $objPage = (app(PageService::class))->wrapCreate($this->testPage);
        $this->assertNotEmpty($objPage->id);

        $response = $this->get('api/comments/'.($objPage->id));
        $response->assertStatus(404);
    }
}
