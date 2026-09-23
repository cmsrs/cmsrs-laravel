<?php

namespace Tests\Feature\Console\Cmsrs;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsrsInstallTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_command_updates_admin_password(): void
    {
        $admin = User::create([
            'email' => 'adm@cmsrs.pl',
            'name' => 'Admin User',
            'role' => User::$role_dict['admin'],
            'password' => 'old-password',
        ]);

        $oldPassword = $admin->password;

        $this->artisan('cmsrs:install')
            ->expectsQuestion(
                'Set admin password (default: cmsrs123)',
                'test123'
            )
            ->expectsConfirmation(
                'Do you want to load demo system data?',
                false
            )
            ->assertExitCode(0);

        $user = User::where('email', 'adm@cmsrs.pl')
            ->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->password);
        $this->assertNotEquals($oldPassword, $user->password);
    }
}
