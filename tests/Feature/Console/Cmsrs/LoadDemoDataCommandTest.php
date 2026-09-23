<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use App\Models\Cmsrs\Cms\Menu;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Shop\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoadDemoDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_demo_data_command_runs_successfully(): void
    {
        $this->artisan('cmsrs:load-demo-data')
            ->assertExitCode(0);

        $this->assertGreaterThan(
            10,
            User::count(),
            'Expected more than 10 users to be created.'
        );
        $this->assertGreaterThan(
            3,
            Page::count(),
            'Expected more than 3 pages to be created.'
        );

        $this->assertGreaterThan(
            3,
            Product::count(),
            'Expected more than 3 products to be created.'
        );
        $this->assertGreaterThan(
            3,
            Menu::count(),
            'Expected more than 3 menus to be created.'
        );

    }
}

/*
declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use App\Models\Cmsrs\Cms\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoadDemoDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_demo_data_command_runs_successfully(): void
    {
        $this->artisan('cmsrs:load-demo-data')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 33);

        $this->assertDatabaseCount('comments', 2);

        $this->assertDatabaseHas('users', [
            'email' => 'client1@cmsrs.pl',
            'name' => 'client1',
            'role' => User::$role_dict['client'],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'client32@cmsrs.pl',
            'name' => 'client32',
            'role' => User::$role_dict['client'],
        ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'First test comment - test1',
        ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'Second test comment - test2',
        ]);
    }

    public function test_load_demo_data_command_creates_32_demo_users(): void
    {
        $this->artisan('cmsrs:load-demo-data')
            ->assertExitCode(0);

        $users = User::where('email', 'like', 'client%@cmsrs.pl')
            ->get();

        $this->assertCount(32, $users);
    }

    public function test_load_demo_data_command_creates_demo_contacts(): void
    {
        $this->artisan('cmsrs:load-demo-data')
            ->assertExitCode(0);

        $this->assertDatabaseCount('contacts', 32);

        $this->assertDatabaseHas('contacts', [
            'email' => 'tt1@cmsrs.pl',
            'message' => 'test contact message1',
        ]);

        $this->assertDatabaseHas('contacts', [
            'email' => 'tt32@cmsrs.pl',
            'message' => 'test contact message32',
        ]);
    }
}

*/
