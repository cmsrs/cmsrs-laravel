<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\MenuService;

#[Signature('cmsrs:load-welcome-page')]
#[Description('load welcome page')]
class LoadWelcomePage extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isWelcomePage = (app(MenuService::class))->isWelcomePage();
        if (! $isWelcomePage) {
            $this->info('Welcome page is not needed. There are already pages or menu items in the system.');
            return Command::SUCCESS;
        }

        $configService = app(ConfigService::class);
        $langs = $configService->arrGetLangs();
        $admUrl = '/admin/';
        $title = array_fill_keys($langs, 'cmsRS welcome page');

        $admUrlLink = "<a href=\"$admUrl\">$admUrl</a>";
        $content = array_fill_keys($langs, "<div class='container pt-5 starter-template  mt-4 mb-4'>
            <h1>Welcome to cmsRS!</h1>
            <p>Thank you for choosing cmsRS!</p>
            <p>You can edit this page in the admin panel in url: $admUrlLink.</p> 
        </div>");

        $mainPage =
        [
            'title' => $title,
            'short_title' => $title,
            'description' => $title,
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'type' => 'main_page',
            'content' => $content,
            'menu_id' => null,
            'page_id' => null,
        ];

        app(PageService::class)->wrapCreate($mainPage);
        return Command::SUCCESS;
    }
}
