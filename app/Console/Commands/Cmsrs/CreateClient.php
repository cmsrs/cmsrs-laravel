<?php

declare(strict_types=1);

namespace App\Console\Commands\Cmsrs;

use App\Models\User;
use Illuminate\Console\Command;

class CreateClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsrs:create-client {client} {pass}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and Edit client';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $client = $this->argument('client');
        $pass = $this->argument('pass');

        $user = User::where('email', $client)->first();

        if ($user) {
            $this->info("Edit user {$client} with password: {$pass}");
            $user->password = $pass;
            $user->save();
        } else {
            $this->info("Create user {$client} with password: {$pass}");
            $user = new User;
            $user->role = User::$role_dict['client'];
            $user->email = $client;
            $user->name = 'Guest';
            $user->password = $pass;
            $user->save();
        }

        return 0;
    }
}
