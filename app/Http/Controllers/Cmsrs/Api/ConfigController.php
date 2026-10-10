<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api;

use App\Http\Controllers\Controller;
use App\Services\Cmsrs\ConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ConfigController extends Controller
{
    public function __construct(
        protected ConfigService $configService,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $config = [];
            $config['page_types'] = ConfigService::arrGetPageTypes();
            $config['langs'] = $this->configService->arrGetLangs();
            $config['cache_enable'] = config('cmsrs.cache_enabled');  // env('CACHE_ENABLE', false);
            $config['is_cache_enable'] = $this->configService->isCacheEnable();
            $config['default_lang'] = $this->configService->getDefaultLang();
            $config['currency'] = $this->configService->getCurrency();
            $config['demo_status'] = $this->configService->getDemoStatus();
            $config['is_shop'] = $this->configService->getIsShop();
            $config['tag_entity_types'] = $this->configService->getTagEntityTypes();
        } catch (\Exception $e) {
            Log::error('config ex: '.$e->getMessage().' line: '.$e->getLine().'  file: '.$e->getFile());

            return response()->json(['success' => false, 'error' => $e->getMessage().' Details in the log file.'], 200);
        }

        return response()->json(['success' => true, 'data' => $config], 200);
    }

    public function clearCache(): JsonResponse
    {
        if (! $this->configService->getConfigCacheEnable()) {
            return response()->json(['success' => false, 'error' => ['clear_cache' => "don't allowed, because cache_enable is false"]]);
        }

        try {
            $this->configService->clearCache();
            // Artisan::call('cache:clear');
        } catch (\Exception $e) {
            Log::error('config ex: '.$e->getMessage().' line: '.$e->getLine().'  file: '.$e->getFile());

            return response()->json(['success' => false, 'error' => $e->getMessage().' Details in the log file.'], 200);
        }

        return response()->json(['success' => true], 200);
    }

    public function createSiteMap(): JsonResponse
    {
        try {
            Artisan::call('cmsrs:create-site-map');
        } catch (\Exception $e) {
            Log::error('config ex: '.$e->getMessage().' line: '.$e->getLine().'  file: '.$e->getFile());

            return response()->json(['success' => false, 'error' => $e->getMessage().' Details in the log file.'], 200);
        }

        return response()->json(['success' => true], 200);
    }

    public function toggleCacheEnableFile(Request $request): JsonResponse
    {
        $action = $request->input('action', null);
        if (! $this->configService->getConfigCacheEnable()) {
            return response()->json(['success' => false, 'error' => ['toggle_cache_enable_file' => "don't allowed, because cache_enable is false"]]);
        }

        if ($action == 'enable') {
            if ($this->configService->createFileCacheEnableIfNotExist()) {
                $this->configService->clearCache();

                return response()->json(['success' => true, 'data' => ['value' => true, 'message' => 'Cache enabled']]);
            }

            return response()->json(['success' => false, 'error' => ['toggle_cache_enable_file' => 'Cache was already enabled']]);
        } elseif ($action == 'disable') {
            if ($this->configService->deleteFileCacheEnableIfExist()) {
                $this->configService->clearCache();

                return response()->json(['success' => true, 'data' => ['value' => false, 'message' => 'Cache disabled']]);
            }

            return response()->json(['success' => false, 'error' => ['toggle_cache_enable_file' => 'Cache was already disabled']]);
        } else {
            return response()->json(['success' => false, 'error' => ['toggle_cache_enable_file' => 'Invalid action']], 400);
        }
    }

    public function isCacheEnable(): JsonResponse
    {
        try {
            $ret = $this->configService->isCacheEnable();
        } catch (\Exception $e) {
            Log::error('is cache enable ex: '.$e->getMessage().' line: '.$e->getLine().'  file: '.$e->getFile());

            return response()->json(['success' => false, 'error' => 'is cache enable problem, details in the log file.'], 200);
        }

        return response()->json(['success' => true, 'data' => ['is_cache_enable' => $ret]], 200);
    }
}
