<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Contact;
// use App\Services\Cmsrs\ContactService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactGuestTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_it_will_create_pl_contact_docs()
    {
        $content = [
            'email' => 'test@example.com',
            'message' => 'test message - test123',
        ];

        $response = $this->post('api/contact/pl', $content);
        $res = $response->getData();

        $this->assertTrue($res->success);
        $msgPl = $res->message;
        $this->assertNotEmpty($msgPl);
    }

    public function test_it_will_create_contact_docs()
    {
        $content = [
            'email' => 'test@example.com',
            'message' => 'test message - test123',
        ];

        $response = $this->post('api/contact/pl', $content);
        $res = $response->getData();

        $this->assertTrue($res->success);
        $msgPl = $res->message;
        $this->assertNotEmpty($msgPl);

        $this->assertEquals(1, Contact::all()->count());
        $item = Contact::all()->first();
        $this->assertEquals($content['email'], $item->email);
        $this->assertEquals($content['message'], $item->message);

        $response = $this->post('api/contact/en', $content);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $msgEn = $res->message;
        $this->assertNotEmpty($msgEn);
        $this->assertNotEquals($msgPl, $msgEn);

        $this->assertEquals(2, Contact::all()->count());
    }

    public function test_it_will_create_contact_wron_lang()
    {
        $content = [
            'email' => 'test@example.com',
            'message' => 'test message - test123',
        ];

        $response = $this->post('api/contact/fr', $content);
        $response->assertStatus(404);
    }

    public function test_it_will_create_contact_wrong_email()
    {
        $content = [
            'email' => 'test@examplecom1',
            'message' => 'test message - test123',
        ];

        $response = $this->post('api/contact/pl', $content);

        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
    }

    public function test_it_will_create_contact_wrong_email_empty()
    {
        $content = [
            'email' => '',
            'message' => 'test message - test123',
        ];

        $response = $this->post('api/contact/pl', $content);

        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
    }

    public function test_it_will_create_contact_wrong_message()
    {
        $content = [
            'email' => 'test@example.com',
            'message' => str_repeat('5', 501),
        ];

        $response = $this->post('api/contact/pl', $content);

        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
    }

    public function test_it_will_create_contact_wrong_message_empty()
    {
        $content = [
            'email' => 'test@example.com',
            'message' => '',
        ];

        $response = $this->post('api/contact/en', $content);

        $res = $response->getData();
        $this->assertFalse($res->success);
        $this->assertNotEmpty($res->error);
    }
}
