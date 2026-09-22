<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Page;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\ImageService;
use App\Services\Cmsrs\Navigation\UrlService;
use App\Services\Cmsrs\Product\ProductDataService;
use App\Services\Cmsrs\Translation\TranslationReader;

class PageViewService
{
    public function __construct(
        private PageDataService $pageDataService,
        private UrlService $urlService,
        private ProductDataService $productDataService,
        private ImageService $imageService,
        private ConfigService $configService,
        private TranslationReader $translationReader
    ) {}

    /**
     * @param  array<string, mixed>  $dataIn
     * @return array<string, mixed>
     */
    public function getDataToView(Page $mPage, array $dataIn): array
    {
        if (empty($dataIn['lang'])) {
            throw new \Exception('Now lang in dataIn');
        }
        $lang = $dataIn['lang'];

        $products = null;
        if ($mPage->type === 'shop') {
            $products = $this->productDataService->getProductsWithImagesByPage($mPage->id);
        }
        $langs = $dataIn['langs'];
        $view = $this->getViewNameByType($mPage);
        $dataDependOnView = $this->getDataDependOnView($mPage, $view, $lang);

        $data = [
            'page' => $mPage,
            'menus' => isset($dataIn['menus']) ? $dataIn['menus'] : null,
            'images' => $this->imageService->getImagesAndThumbsByTypeAndRefId('page', $mPage->id),
            'h1_title' => $this->translationReader->translatesByColumnAndLang($mPage, 'title', $lang) ?? config('app.name', 'cmsRS'),
            'content' => $this->translationReader->translatesByColumnAndLang($mPage, 'content', $lang),
            'seo_description' => $this->translationReader->translatesByColumnAndLang($mPage, 'description', $lang) ?? config('app.name', 'cmsRS'),
            'products' => $products,
            'lang' => $lang,
            'langs' => $langs,
            're_public' => config('cmsrs.recaptcha.public'),  // env('GOOGLE_RECAPTCHA_PUBLIC', ''),
            'view' => 'cmsrs.'.$view,
            'companyData' => $this->pageDataService->getPageDataByShortTitleCache('company_data', 'content', $lang),
        ];

        return array_merge($data, $dataDependOnView, $dataIn);
    }

    private function getViewNameByType(Page $mPage): string
    {
        $type = $mPage->type;
        if ($type == 'projects') {
            $view = 'projects';
        } elseif ($type == 'clear') {
            $view = 'clear';
        } elseif ($type == 'privacy_policy') { // it is used in footer, not related in menu
            $view = 'in'; // (before: 'in' ) it can be cms (each language have got own language, not one language in each pages)
        } elseif ($type == 'gallery') {
            $view = 'gallery';
        } elseif ($type == 'shop') {
            $view = 'shop';
            // } elseif ($type == 'checkout') {
            //     $view = 'checkout';
            // } elseif ($type == 'register') {
            //     $view = 'register';
            // } elseif ($type == 'home') {
            //     $view = 'home';
            // } elseif ($type == 'shoppingsuccess') {
            //     $view = 'shoppingsuccess';
            // } elseif ($type == 'search') {
            //     $view = 'search';
            // } elseif ($type == 'forgot') {
            //     $view = 'forgot';
        } else {
            $view = 'cms';
        }

        return $view;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDataDependOnView(Page $mPage, string $view, string $lang): array
    {
        $data = [];
        if ($view == 'projects') {
            $data['content_default_lang'] = $this->translationReader->translatesByColumnAndLang($mPage, 'content', $this->configService->getDefaultLang());
        } elseif ($view == 'shop') {
            $data['page_url'] = $this->urlService->getUrl($mPage, $lang);
        }

        return $data;
    }
}
