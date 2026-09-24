<?php

declare(strict_types=1);

namespace App\Console\Commands\Cmsrs;

use App\Models\User;
use Illuminate\Console\Command;

class ChangeAdminPass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsrs:change-admin-pass {pass}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change admin password';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pass = $this->argument('pass');

        if (! $pass) {
            echo 'set admin password';
            exit;
        }

        $roleAdm = User::$role_dict['admin'];
        $user = User::where('role', $roleAdm)->first();
        if ($user) {
            $user->password = $pass;
            $user->save();
        } else {
            echo "can't find admin user";
            exit;
        }

        return 0;
    }
}
