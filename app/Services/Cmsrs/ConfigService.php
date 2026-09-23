<?php

declare(strict_types=1);

namespace App\Services\Cmsrs;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ConfigService
{
    const PAGE_TYPES_STR_DEFAULT = 'cms,gallery,shop,contact,main_page,privacy_policy,projects,clear,inner'; // default values

    const LANG_DEFAULT = 'en,pl';

    const PAGINATION_DEFAULT = 10;

    const SORT_ASC = 'asc';

    const SORT_DESC = 'desc';

    const CURRENCY_DEFAULT = 'USD';

    const CACHE_ENABLE_FILE_DEFAULT = 'app/cache_enable.txt';

    const ALLOWED_UPLOAD_FILE_EXT_DEFAULT = 'jpg,jpeg,png,webp';

    const COOKIE_FRONT_LOGIN_LANG_NAME = 'front_login_lang';

    /**
     * @var string
     */
    private $langs;

    /**
     * @var string
     */
    private $cacheEnableFile;

    /**
     * @var string
     */
    private $filePath;

    public function __construct()
    {
        $this->langs = config('cmsrs.langs');  // env('LANGS', ConfigService::LANG_DEFAULT); // empty(env('LANGS')) ? Config::LANG_DEFAULT : env('LANGS');
        $this->cacheEnableFile = config('cmsrs.cache_enable_file'); // env('CACHE_ENABLE_FILE', ConfigService::CACHE_ENABLE_FILE_DEFAULT);
        $this->filePath = $this->getCacheEnableFilePath();
    }

    /**
     * @return array<int, string>
     */
    public function arrAllowedUploadFileExt(): array
    {
        return explode(',', $this->allowedUploadFileExt());
    }

    private function allowedUploadFileExt(): string
    {
        return config('cmsrs.allowed_upload_extensions');
        // return env('ALLOWED_UPLOAD_EXTENSIONS', ConfigService::ALLOWED_UPLOAD_FILE_EXT_DEFAULT);
    }

    public function getCacheFilePath(): string
    {
        return $this->filePath;
    }

    public function getIsShop(): bool
    {
        return config('cmsrs.features.shop'); // env('IS_SHOP', true);
    }

    public function getDemoStatus(): bool
    {
        return config('cmsrs.demo'); // env('DEMO_STATUS', false);
    }

    public function getCurrency(): string
    {
        return config('cmsrs.currency'); // env('CURRENCY', ConfigService::CURRENCY_DEFAULT);
    }

    public function getCacheEnableFilePath(): string
    {
        return storage_path($this->cacheEnableFile);
    }

    public function isExistCacheFileEnable(): bool
    {
        return File::exists($this->filePath);
    }

    public function deleteFileCacheEnableIfExist(): bool
    {
        if (File::exists($this->filePath)) {
            File::delete($this->filePath);

            return true;
        }

        return false;
    }

    public function createFileCacheEnableIfNotExist(): bool
    {
        if (! File::exists($this->filePath)) {
            File::put($this->filePath, '');

            return true;
        }

        return false;
    }

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
    }

    public function setLangs(string $langs): void
    {
        $this->langs = $langs;
    }

    public function getLangs(): string
    {
        return $this->langs;
    }

    public static function getPagination(): int
    {
        return config('cmsrs.pagination'); // env('PAGINATION', ConfigService::PAGINATION_DEFAULT);
    }

    public static function getPageTypes(): string
    {
        return config('cmsrs.page_types'); // env('PAGE_TYPES', ConfigService::PAGE_TYPES_STR_DEFAULT);
    }

    /**
     * @return array<int, string>
     */
    public static function arrGetPageTypes(): array
    {
        $strPageTypes = ConfigService::getPageTypes();

        return explode(',', $strPageTypes);
    }

    public function getLangsFromEnv(): string
    {
        $langs = '';
        if ($this->getLangs()) {
            $langs = $this->getLangs();
        } else {
            $langs = ConfigService::LANG_DEFAULT;
            // throw new \Exception("You must set at least one language in the .env file");
        }

        return $langs;
    }

    /**
     * @return array<int, string>
     */
    public function arrGetLangs()
    {
        $strLangs = ConfigService::getLangsFromEnv();

        return explode(',', $strLangs);
    }

    public function isManyLangs(): bool
    {
        return count($this->arrGetLangs()) > 1;
    }

    /**
     * @return array<int, string>
     */
    public static function arrGetLangsEnv(): array
    {
        // env('LANGS', ConfigService::LANG_DEFAULT)
        return explode(',', config('cmsrs.langs'));
    }

    public static function getDefaultLang(): string
    {
        $langs = ConfigService::arrGetLangsEnv();
        if (empty($langs) || empty($langs[0])) {
            // $langs = [];
            // $langs[0] =  'en';//ConfigService::LANG_DEFAULT;
            throw new \Exception('You must set at least one language in the .env file (default lang)');
        }

        return $langs[0];
    }

    public function getConfigCacheEnable(): bool
    {
        return config('cmsrs.cache_enabled'); // env('CACHE_ENABLE', false);
    }

    public function isCacheEnable(): bool
    {
        $formEnv = $this->getConfigCacheEnable();
        $isFileExist = $this->isExistCacheFileEnable();

        // dump( 'formEnv='.$formEnv.' isFileExist ='.$isFileExist);

        return $formEnv && $isFileExist;
    }
}
