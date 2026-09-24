<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Navigation;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\Cms\MenuService;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\CacheManagerService;
use App\Services\Cmsrs\Translation\TranslationReader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UrlService
{
    public function __construct(
        private ConfigService $configService,
        private MenuService $menuService,
        private CacheManagerService $cacheManagerService,
        private TranslationReader $translationReader
    ) {}

    public function getUrl(Page $mPage, string $lang, ?string $urlParam = null): ?string
    {
        $type = $mPage->type;
        if ($type == 'inner') {
            return null;
        } elseif ($type == 'main_page') {
            return $this->getMainUrl($lang);

            // TODO!!! - it should be removed
            // } elseif (($type == 'login') || ($type == 'checkout') || ($type == 'register') || ($type == 'home') || ($type == 'shoppingsuccess') || ($type == 'search') || ($type == 'forgot')) {
            //    return $this->getTypeUrl($type, $lang);
        }

        // elseif ('privacy_policy' == $this->type) {
        //    return $this->getIndependentUrl($lang);
        // }
        return $this->getCmsUrl($mPage, $lang, $urlParam);
    }

    public function getUrlByPageOrRouteName(?Page $mPage, string $lang, ?string $productSlug = null, ?string $routeName = null): string
    {
        // return (! empty($mPage)) ? $this->getUrl($mPage, $lang, $productSlug) : route($routeName, ['lang' => $lang]);
        if ($mPage !== null) {
            return $this->getUrl($mPage, $lang, $productSlug) ?? '';
        }

        if ($routeName === null) {
            throw new \InvalidArgumentException('routeName cannot be null when page is null');
        }

        return route($routeName, ['lang' => $lang]);
    }

    /**
     * Get all URLs for a given page or route name across all supported languages.
     *
     * @param  array<string, string>|null  $productSlug
     * @return array<string, string> An associative array where keys are language codes and values are URLs.
     */
    public function getAllUrlsByPageOrRouteName(?Page $mPage, ?array $productSlug = null, ?string $routeName = null): array
    {

        if ($routeName == 'shoppingsuccess') { // TODO - manual tests
            return [];
        }

        $urls = [];
        $langs = $this->configService->arrGetLangs();
        foreach ($langs as $l) {
            $productSlugForLang = $productSlug[$l] ?? null;
            $urls[$l] = $this->getUrlByPageOrRouteName($mPage, $l, $productSlugForLang, $routeName);
        }

        return $urls;
    }

    /**
     * use in headless
     *
     * @return array<string, string>
     */
    public function getUrls(Page $mPage, ?string $urlParam = null): array
    {
        $urls = [];
        $langs = $this->configService->arrGetLangs();
        foreach ($langs as $l) {
            $urls[$l] = $this->getUrl($mPage, $l, $urlParam) ?? '';
        }

        return $urls;
    }

    public function getSlugByLang(Page $model, string $lang): ?string
    {
        $column = 'title';
        $name = $this->translationReader->translatesByColumnAndLang($model, $column, $lang);

        if ($name === null || $name === '') {
            throw new \RuntimeException("I cant create slug for page column: $column for lang: $lang, because value is empty");
        }

        return Str::slug($name, '-');
    }

    private function getCmsUrl(Page $mPage, string $lang, ?string $urlParam = null): string
    {
        $menuSlug = $this->getMenuSlugByLangCache($mPage, $lang);

        if (empty($menuSlug)) {
            return $this->getIndependentUrl($mPage, $lang);
        }

        $countPages = $this->getNumPagesBelongsToThisMenuCache($mPage);
        if (($countPages == 1) && ($mPage->type != 'shop')) {
            $url = '/'.Page::PREFIX_CMS_ONE_PAGE_IN_MENU_URL.'/'.$menuSlug;
        } else {
            $url = '/'.Page::PREFIX_CMS_URL.'/'.$menuSlug.'/'.$this->getSlugByLang($mPage, $lang);
        }
        if ($urlParam) {
            // $url = $url."/".Str::slug($urlParam, '-');
            $url = $url.'/'.$urlParam;
        }
        $langs = ConfigService::arrGetLangsEnv();
        if (count($langs) > 1) {
            $url = '/'.$lang.$url;
        }

        return $url;
    }

    private function getMainUrl(string $lang): string
    {
        $langs = ConfigService::arrGetLangsEnv();
        array_shift($langs); // after this langs will be changed. It has rest of langs without first one.

        if (empty($langs)) {
            $url = '/';
        } else {
            $url = in_array($lang, $langs) ? '/'.$lang : '/';
        }

        return $url;
    }

    private function getIndependentUrl(Page $mPage, string $lang): string
    {
        $url = '/'.Page::PREFIX_IN_URL.'/'.$this->getSlugByLang($mPage, $lang);
        $langs = ConfigService::arrGetLangsEnv();
        if (count($langs) > 1) {
            $url = '/'.$lang.$url;
        }

        return $url;
    }

    private function getNumPagesBelongsToThisMenu(Page $mPage): ?int
    {
        $menu = $mPage->menu()->first();
        if (empty($menu)) {
            return null;
        }

        return $this->menuService->pagesPublishedAndAccess($menu, Auth::check())->count();
    }

    private function getNumPagesBelongsToThisMenuCache(Page $mPage): ?int
    {
        $key = $this->cacheManagerService->key(
            'countpagesinthismenu',
            (string) $mPage->id
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getNumPagesBelongsToThisMenu($mPage)
        );
    }

    private function getMenuSlugByLangCache(Page $mPage, string $lang): ?string
    {
        $key = $this->cacheManagerService->key(
            'menusluglang',
            (string) $mPage->id,
            $lang
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getMenuSlugByLang($mPage, $lang)
        );
    }

    private function getMenuSlugByLang(Page $mPage, string $lang): ?string
    {
        $menu = $mPage->menu()->first();

        if (empty($menu)) {
            return null;
        }

        return $this->menuService->getSlugByLang($menu, $lang);
    }
}
