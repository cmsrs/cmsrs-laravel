<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Page;

use App\Models\Cmsrs\Cms\Image;
use App\Models\Cmsrs\Cms\Menu;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Cms\Translate;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\CacheManagerService;
use App\Services\Cmsrs\ImageService;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Navigation\UrlService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PageDataService
{
    public function __construct(private ConfigService $configService, private MenuService $menuService, private ImageService $imageService, private CacheManagerService $cacheManagerService, private PageService $pageService, private UrlService $urlService) {}

    public function getPageDataByShortTitleCache(string $shortTitle, string $data = 'content', ?string $lang = null): ?string
    {
        if (empty($lang)) {
            $lang = $this->configService->getDefaultLang();
        }

        $key = $this->cacheManagerService->key(
            'page_by_short_title_'.$data,
            $shortTitle,
            $lang
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getPageDataByShortTitle($shortTitle, $data, $lang)
        );
    }

    /**
     * @return Collection<int, Image>
     */
    public function getPageDataImagesByShortTitleCache(string $shortTitle): Collection
    {
        $key = $this->cacheManagerService->key(
            'page_by_short_title_images',
            $shortTitle,
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getPageDataImagesByShortTitle($shortTitle)
        );
    }

    /**
     * @param  Collection<int, Menu>  $menus
     */
    public function getPageBySlugCache(Collection $menus, string $menuSlug, ?string $pageSlug, string $lang): ?Page
    {
        $key = $this->cacheManagerService->key(
            'page',
            $menuSlug.'_'.$pageSlug,
            $lang
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getPageBySlug($menus, $menuSlug, $pageSlug, $lang)
        );
    }

    /**
     * TODO - it is not use right now. - to remove...
     */
    public function getContentInnerPageByIdCache(int $pageId, ?string $lang = null): string
    {
        if (empty($lang)) {
            $lang = ConfigService::getDefaultLang();
        }

        $key = $this->cacheManagerService->key(
            'pageinner_by_pageid',
            (string) $pageId,
            $lang
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getContentInnerPageByPageIdAndLang($pageId, $lang)
        );

    }

    /**
     * TODO - it is not use right now. - to remove...
     */
    private function getContentInnerPageByPageIdAndLang(int $pageId, string $lang): string
    {
        $page = Page::findOrFail($pageId);

        $contents = $page->contents->pluck('value', 'lang')->toArray();

        return empty($contents[$lang]) ? '' : $contents[$lang];
    }

    /**
     * @return Collection<int, Image>
     */
    private function getPageDataImagesByShortTitle(string $shortTitle): Collection
    {
        $page = $this->getPageByShortTitle($shortTitle);

        if ($page === null) {
            return new Collection;
        }

        return $this->imageService->getImagesAndThumbsByTypeAndRefId('page', $page->id);
    }

    private function getPageDataByShortTitle(string $shortTitle, string $data = 'content', ?string $lang = null): ?string
    {
        if (! in_array($data, ['content', 'title', 'images', 'url'])) {
            throw new \Exception('second param is: content title images and url allowed, but now is: '.$data);
        }

        if ($lang === null) {
            $lang = ConfigService::getDefaultLang();
        }

        $page = $this->getPageByShortTitle($shortTitle);

        if ($page === null) {
            return null;
        }

        if ($data == 'url') {
            return $this->urlService->getUrl($page, $lang);
        }

        $pageData = $this->pageService->getAllPagesWithImagesOneItem($page);

        $dataByLang = empty($pageData[$data]) ? '' : $pageData[$data];

        return empty($dataByLang[$lang]) ? '' : $dataByLang[$lang];
    }

    /**
     * @param  Collection<int, Menu>  $menus
     */
    private function getPageBySlug(Collection $menus, string $menuSlug, ?string $pageSlug, string $lang): ?Page
    {
        $pageOut = null;

        foreach ($menus as $menu) {
            if ($menuSlug == $this->menuService->getSlugByLang($menu, $lang)) {
                $objPagesPublishedAndAccess = $this->menuService->pagesPublishedAndAccess($menu, Auth::check());
                if ($objPagesPublishedAndAccess->count() == 1) { // it is the case for pageSlug = null, 1 page in menu
                    $pageOut = $objPagesPublishedAndAccess->first();
                    break;
                }

                $pagesPublished = $this->menuService->pagesPublished($menu);
                foreach ($pagesPublished as $page) {
                    if ($pageSlug == $this->urlService->getSlugByLang($page, $lang)) {
                        $pageOut = $page;
                        break;
                    }
                }
            }
        }

        return $pageOut;
    }

    private function getPageByShortTitle(string $shortTitle): ?Page
    {
        $translate = Translate::where('value', '=', $shortTitle)->where('column', '=', 'short_title')->first();  // where('lang', '=', $defaultLang )->first();
        if (empty($translate)) {
            return null;
        }

        // ->where('type', '=', 'inner') //todo why is this condition ? 'published', '=', 1 - is it make sense (see inner page post:/api/pages)? see test: it_will_get_data_page_by_short_title
        $page = $translate->page()->where('published', '=', 1)->first();
        if (empty($page)) {
            return null;
        }

        return $page;
    }
}
