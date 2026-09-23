<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_client_is_created(): void
    {
        $this->artisan('cmsrs:create-client', [
            'client' => 'client@test.pl',
            'pass' => 'test123',
        ])
            ->assertExitCode(0);

        $user = User::where('email', 'client@test.pl')->first();

        $this->assertNotNull($user);
        $this->assertEquals('Guest', $user->name);
        $this->assertEquals(User::$role_dict['client'], $user->role);
        $this->assertEquals('client@test.pl', $user->email);

    }

    public function test_existing_client_password_is_updated(): void
    {
        $client = User::create([
            'email' => 'client@test.pl',
            'name' => 'Old Name',
            'role' => User::$role_dict['client'],
            'password' => 'old-password',
        ]);

        $oldPassword = $client->password;

        $this->artisan('cmsrs:create-client', [
            'client' => 'client@test.pl',
            'pass' => 'new-password',
        ])
            ->assertExitCode(0);

        $client->refresh();

        $this->assertNotEquals(
            $oldPassword,
            $client->password
        );
    }

    public function test_existing_client_is_not_duplicated(): void
    {
        User::create([
            'email' => 'client@test.pl',
            'name' => 'Existing Client',
            'role' => User::$role_dict['client'],
            'password' => 'old-password',
        ]);

        $this->artisan('cmsrs:create-client', [
            'client' => 'client@test.pl',
            'pass' => 'new-password',
        ])
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
    }
}
