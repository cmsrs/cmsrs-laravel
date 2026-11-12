<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class HomeTest extends Base
{
    use RefreshDatabase;

    // private $testData;

    // private $testDataMenu;

    // private $menuId;

    // private $menuObj;

    // private $titleEn = 'eeeeeeeeeeeeeeeennnnnnnnnnnnnnnnn';

    // private $titlePl = 'pppppppppppppppplllllllllllllllll';

    // private $langs;

    protected function setUp(): void
    {
        putenv('LANGS="pl,en"');
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
        // $this->createUser();
        $this->createClientUser();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_it_will_api_home_token()
    {
        $this->assertAuthenticated();
        $token = User::getTokenForClient();
        $this->assertNotEmpty($token);

        $user = Auth::user();
        $check = $user->checkClientByToken($token);
        $this->assertTrue($check);

        $staticCheck = User::checkApiClientByToken($token);
        $this->assertTrue($staticCheck);
    }
}
