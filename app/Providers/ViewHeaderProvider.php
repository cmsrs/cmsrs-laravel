<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Cmsrs\Cms\Page\PageService;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\RequestService;
use App\Services\Cmsrs\Navigation\NavigationService;
use App\Services\Cmsrs\Navigation\UrlService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewHeaderProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('includes.header', function ($view) {
            $configService = app(ConfigService::class);
            $pageService = app(PageService::class);
            $urlService = app(UrlService::class);
            $navigationService = app(NavigationService::class);
            $requestService = app(RequestService::class);

            $lang = $requestService->getLangFromRequest();
            $langs = $configService->arrGetLangs();
            $manyLangs = $configService->isManyLangs();

            $mainPage = $pageService->getFirstPageByTypeCache('main_page');
            $urlMainPage = $mainPage
                ? $urlService->getUrl($mainPage, $lang)
                : '/';

            $data = $view->getData();
            $page = $data['page'] ?? null;   //
            $productNameSlug = $data['product_name_slug'] ?? null;
            $routeName = request()->route()?->getName(); //

            $allUrlsByPageOrRouteName = $urlService->getAllUrlsByPageOrRouteName($page, $productNameSlug, $routeName);

            $view->with([
                'treeMenu' => $navigationService->getNavigationTreeCache(Auth::check()),
                'lang' => $lang,
                'langs' => $langs,
                'manyLangs' => $manyLangs,

                'currency' => $configService->getCurrency(),
                'urlMainPage' => $urlMainPage,
                'allUrlsByPageOrRouteName' => $allUrlsByPageOrRouteName,

            ]);
        });

        View::composer('includes.*footer', function ($view) {

            $pageService = app(PageService::class);
            $requestService = app(RequestService::class);

            $lang = $requestService->getLangFromRequest();
            $footerPages = $pageService->getFooterPages($lang);

            $view->with([
                'lang' => $lang,
                'footerPages' => $footerPages,
            ]);
        });

        View::composer('layouts.default', function ($view) {

            $pageService = app(PageService::class);
            $requestService = app(RequestService::class);

            $lang = $requestService->getLangFromRequest();
            $data = $view->getData();
            $page = $data['page'] ?? null;   //

            $imagesGlobal = [];
            if ($page && $page->type === 'gallery') {
                $imagesGlobal = $pageService->arrImages($page, $lang);
            }

            $view->with('imagesGlobal', $imagesGlobal);
        });

    }
} // END OF CLASS
