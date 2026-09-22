<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Navigation\UrlService;
use App\Services\Cmsrs\Product\ProductService;
use Illuminate\Console\Command;

class CreateSiteMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsrs:create-site-map';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create site map (the script creates a sitemap in txt format)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private readonly ConfigService $configService,
        private readonly UrlService $urlService,
        private readonly ProductService $productService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // $appUrl = env('APP_URL');
        $appUrl = config('app.url');
        $langs = $this->configService->arrGetLangs();
        $pages = Page::where('after_login', '=', 0)->where('published', '=', 1)->where('type', '!=', 'inner')->get();
        $prodUrls = $this->productService->getProductsUrl();

        $strUrls = '';
        foreach ($langs as $lang) {
            foreach ($pages as $page) {
                $strUrls .= $appUrl.$this->urlService->getUrl($page, $lang)."\n";
            }
            foreach ($prodUrls as $prodUrl) {
                $strUrls .= $appUrl.$prodUrl[$lang]."\n";
            }
        }

        $isLogin = config('cmsrs.features.login');  // env('IS_LOGIN', true);
        $isRegister = config('cmsrs.features.register');  // env('IS_REGISTER', true);
        $isManyLangs = $this->configService->isManyLangs();
        if ($isManyLangs) {
            if ($isLogin) {
                foreach ($langs as $lang) {
                    $strUrls .= route('login', ['lang' => $lang])."\n";
                }
            }
            if ($isRegister) {
                foreach ($langs as $lang) {
                    $strUrls .= route('register', ['lang' => $lang])."\n";
                }
            }
            foreach ($langs as $lang) {
                // $strUrls .= $appUrl.route('shoppingsuccess', ['lang' => $lang])."\n";//after login
                $strUrls .= route('checkout', ['lang' => $lang])."\n";
                $strUrls .= route('search', ['lang' => $lang])."\n";
            }
        } else {
            if ($isLogin) {
                $strUrls .= route('login')."\n";
            }
            if ($isRegister) {
                $strUrls .= route('register')."\n";
            }
            // $strUrls .= $appUrl.route('shoppingsuccess')."\n"; //after login
            $strUrls .= route('checkout')."\n";
            $strUrls .= route('search')."\n";
        }

        $siteMapPath = public_path().'/sitemap.txt';
        file_put_contents($siteMapPath, $strUrls);

        return 0;
    }
}
