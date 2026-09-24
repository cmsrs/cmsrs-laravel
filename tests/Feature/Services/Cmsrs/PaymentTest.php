<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Services\Cmsrs\Shop\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('LANGS="en"');
        putenv('API_SECRET=""');
        putenv('CURRENCY="USD"');
        putenv('CACHE_ENABLE=false');
        putenv('CACHE_ENABLE_FILE="app/cache_enable_test.txt"');
        putenv('DEMO_STATUS=false');
        putenv('IS_SHOP=true');
        putenv('IS_LOGIN=false'); // !!
        putenv('IS_REGISTER=false'); // !!
        putenv('IS_HEADLESS=false');
        putenv('IS_SSR=true');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    use RefreshDatabase;

    public function test_get_payment()
    {
        $all = PaymentService::getPayment();
        $this->assertIsArray($all);
        $this->assertNotEmpty($all);
    }

    public function test_get_one_payment()
    {
        $key = PaymentService::KEY_CASH;
        $payment = PaymentService::getPayment($key);
        $this->assertIsArray($payment);
        $this->assertIsString($payment['name']);
        $this->assertNotEmpty($payment);
    }
}
