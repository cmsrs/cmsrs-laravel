<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeAdminPassTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_password_is_changed(): void
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'adm@cmsrs.pl',
            'role' => User::$role_dict['admin'],
            'password' => 'old-password',
        ]);

        $oldPassword = $admin->password;

        $this->artisan('cmsrs:change-admin-pass', [
            'pass' => 'new-password',
        ])
            ->assertExitCode(0);

        $admin->refresh();

        $this->assertNotEquals(
            $oldPassword,
            $admin->password
        );
    }
}
