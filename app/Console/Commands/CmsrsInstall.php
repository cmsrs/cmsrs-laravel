<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cmsrs:install')]
#[Description('Command description')]
class CmsrsInstall extends Command
{
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // INFO only (no editing allowed)
        $this->info('Admin account is fixed: adm@cmsrs.pl');

        // Ask only for password
        $password = $this->secret('Set admin password (default: cmsrs123)');

        if (! $password) {
            $password = 'cmsrs123';
        }

        $roleAdm = User::$role_dict['admin'];

        $user = User::where('email', 'adm@cmsrs.pl')
            ->where('role', $roleAdm)
            ->first();

        if (! $user) {
            $this->error('Admin user (adm@cmsrs.pl) not found.');

            return 1;
        }

        $user->password = $password;
        $user->save();

        $this->info('Admin password updated successfully.');

        // Demo data question
        $this->newLine();

        $demo = $this->confirm('Do you want to load demo system data?', true);

        if ($demo) {
            $this->info('Loading demo data...');
            $this->info('Please wait...');
            $this->call('cmsrs:load-demo-data');
            $this->info('Demo data loaded successfully.');
        }else {
            $this->info('cmsrs:load-welcome-page');
        }

        $this->info('');
        $this->info('====================================');
        $this->info('CMSRS INSTALLATION COMPLETE');
        $this->info('====================================');
        $this->info('');
        $this->info('Installation successful! CMSRS is ready to use.');
        $this->info('');
        $this->info('Admin panel: http://127.0.0.1:8000/admin/');
        $this->info('');
        $this->info('Login: adm@cmsrs.pl');
        $this->info('Password: cmsrs123 (default) or the one you set');
        $this->info('');
        $this->info('Next step: php artisan serve');
        $this->info('');

        return 0;
    }
}
