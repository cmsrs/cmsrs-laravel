<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\Cms\MenuService;
use App\Services\Cmsrs\Cms\Page\PageService;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\HeadlessService;
use App\Services\Cmsrs\Navigation\NavigationService;
use App\Services\Cmsrs\Shop\Product\ProductDataService;
use App\Services\Cmsrs\Shop\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HeadlessController extends Controller
{
    public function __construct(
        protected ConfigService $configService,
        protected PageService $pageService,
        protected MenuService $menuService,
        protected HeadlessService $headlessService,
        protected NavigationService $navigationService,
        protected ProductService $productService,
        protected ProductDataService $productDataService,
    ) {}

    public function getPagesByShortTitle(Request $request, string $shortTitle): JsonResponse
    {
        if (empty($shortTitle)) {
            return response()->json(['success' => false, 'error' => 'Short title is required'], 200);
        }

        $pages = $this->headlessService->getPagesByShortTitleWithImages($shortTitle);

        return response()->json(['success' => true, 'data' => $pages], 200);
    }

    public function getAllPagesByType(Request $request, string $type): JsonResponse
    {
        if (! in_array($type, ConfigService::arrGetPageTypes())) {
            return response()->json(['success' => false, 'error' => 'wrong type'], 200);
        }

        $pages = $this->headlessService->getAllPagesWithImages($type);

        return response()->json(['success' => true, 'data' => $pages], 200);
    }

    public function onePageItemByLang(Request $request, Page $page, string $lang): JsonResponse
    {
        if (! in_array($lang, ConfigService::arrGetLangsEnv())) {
            return response()->json(['success' => false, 'error' => 'wrong lang'], 200);
        }

        if ($page->after_login) {
            return response()->json(['success' => false, 'error' => 'Unauthorized access'], 403);
        }

        if (! $page->published) {
            return response()->json(['success' => false, 'error' => 'Page not published'], 403);
        }

        $onePage = $this->headlessService->getAllPagesWithImagesOneItemByLang($page, $lang);

        if ($onePage['type'] == 'shop') {
            $onePage['products'] = $this->productDataService->getGivenProductsWithImagesByPageId($page->id, false, $lang);
        }

        return response()->json(['success' => true, 'data' => $onePage], 200);
    }

    public function getMenus(Request $request): JsonResponse
    {
        $menus = $this->navigationService->getNavigationTreeCache();

        return response()->json(['success' => true, 'data' => $menus], 200);
    }

    public function config(): JsonResponse
    {
        try {
            $config = [];
            $config['langs'] = $this->configService->arrGetLangs();
            $config['default_lang'] = $this->configService->getDefaultLang();
            $config['cache_enable'] = config('cmsrs.cache_enabled');  // env('CACHE_ENABLE', false);
            $config['is_cache_enable'] = $this->configService->isCacheEnable();
            $config['currency'] = $this->configService->getCurrency();
            $config['demo_status'] = $this->configService->getDemoStatus();
            $config['is_shop'] = $this->configService->getIsShop();
        } catch (\Exception $e) {
            Log::error('headless config ex: '.$e->getMessage().' line: '.$e->getLine().'  file: '.$e->getFile());

            return response()->json(['success' => false, 'error' => $e->getMessage().' Details in the log file.'], 200);
        }

        return response()->json(['success' => true, 'data' => $config], 200);
    }
}
