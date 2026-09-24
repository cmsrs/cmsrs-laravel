<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Shop\Product;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Shop\Product;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\CacheManagerService;
use App\Services\Cmsrs\Helpers\LangHelperService;
use App\Services\Cmsrs\Helpers\PriceHelperService;
use App\Services\Cmsrs\ImageService;
use App\Services\Cmsrs\Navigation\UrlService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductDataService
{
    /** @var array<int, string> */
    public array $productFields;

    public function __construct(
        private UrlService $urlService,
        private ImageService $imageService,
        private PriceHelperService $priceHelperService,
        private CacheManagerService $cacheManagerService
    ) {

        $this->productFields = [
            'id',
            'published',
            'sku',
            'price',
            'page_id',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllProductsWithImagesByLangCache(string $lang): array
    {
        $key = $this->cacheManagerService->key(
            'products',
            'name_price',
            $lang
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getAllProductsWithImagesByLang($lang)
        );

    }

    /**
     * function use on the frontend
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAllProductsWithImagesByLang(string $lang): array
    {
        $products = $this->getAllProductsWithImages(true);

        $out = [];
        foreach ($products as $product) {
            if (! empty($product['published'])) {
                $productId = $product['id'];
                // $out[$productId]["product_id"] = $productId;
                $out[$productId]['price'] = $product['price'];
                $out[$productId]['price_description'] = $this->priceHelperService->getPriceDescriptionWrap($product['price']);
                $out[$productId]['name'] = $product['product_name'][$lang];
                $out[$productId]['url_product'] = $product['url_product'][$lang];
                if (! empty($product['images']) && ! empty($img = $product['images']->first())) {
                    $out[$productId]['url_image'] = $img->fs['small'];
                }
            }
        }

        return $out;
    }

    // TODO - cache -- maybe..
    /**
     * @return array<int, mixed>
     */
    public function getAllProductsWithImages(bool $withUrls = false): array
    {
        $products = $this->getAllProductsWithTranslates();

        return $this->getAllProductsWithImagesArr($products, $withUrls);
    }

    /**
     * @return Collection<int, Product>
     */
    public function getAllProductsWithTranslates(): Collection
    {
        return Product::with(['translates', 'contents'])->orderBy('id', 'asc')->get();
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<int, array<string, mixed>>
     */
    private function getAllProductsWithImagesArr(Collection $products, bool $withUrls = false, ?string $lang = null): array
    {
        $i = 0;
        $out = [];
        foreach ($products as $product) {
            $out[$i] = $this->getProductDataByProductArr($product, $lang);

            if ($withUrls) {
                $urls = $this->getProductUrls($product);
                $out[$i] = array_merge($out[$i], $urls);
            }
            $i++;
        }

        return $out;
    }

    /**
     * @return array<int, mixed>
     */
    public function getGivenProductsWithImagesByPageId(int $pageId, bool $withUrls = false, ?string $lang = null): array
    {
        $products = $this->getDataProductsWithImagesByPage($pageId);

        return $this->getAllProductsWithImagesArr($products, $withUrls, $lang);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductDataByProductArr(Product $product, ?string $lang = null): array
    {

        $out = [];
        $out = $this->getProductDataFormat($product);
        $out['product_name_default_lang'] = $this->getProductNameDefaultLang($out);
        if ($lang) {
            $out = LangHelperService::removeKeyLangInArr($out, $lang);
        }

        $out['images'] = $this->imageService->getImagesAndThumbsByTypeAndRefId('product', $product->id, $lang);

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductDataFormat(Product $product): array
    {

        $out = [];
        foreach ($this->productFields as $field) {
            $out[$field] = $product[$field];
        }
        $price = ! empty($product['price']) ? $product['price'] : 0;
        $out['price_description'] = $this->priceHelperService->getPriceDescriptionWrap($price);
        foreach ($product['translates'] as $translate) {
            $out[$translate['column']][$translate['lang']] = $translate['value'];
            if ($translate['column'] == 'product_name') {
                $out['product_name_slug'][$translate['lang']] = Str::slug($translate['value'], '-');
            }
        }
        foreach ($product['contents'] as $translate) {
            $out[$translate['column']][$translate['lang']] = $translate['value'];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $arrProductFormat
     */
    private function getProductNameDefaultLang(array $arrProductFormat): string
    {
        $lang = ConfigService::getDefaultLang();

        return $arrProductFormat['product_name'][$lang];
    }

    /**
     * @return Collection<int, Product>
     */
    private function getDataProductsWithImagesByPage(int $pageId): Collection
    {
        return Product::with(['translates', 'contents'])->where('page_id', $pageId)->orderBy('id', 'asc')->where('published', '=', 1)->get(); // ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    public function getProductsWithImagesByPage(int $pageId): array
    {
        $products = $this->getDataProductsWithImagesByPage($pageId);

        return $this->dataToRender($products);
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<int, array<string, mixed>>
     */
    public function dataToRender(Collection $products): array
    {
        $i = 0;
        $out = [];

        foreach ($products as $product) {
            $urls = $this->getProductUrls($product);
            $out[$i] = array_merge($this->getProductDataByProductArr($product), $urls);
            $i++;
        }

        return $out;
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getProductUrls(Product $productWithTranslate): array
    {
        $out = [];
        $arrProduct = $this->getProductDataFormat($productWithTranslate);
        $langs = ConfigService::arrGetLangsEnv();

        foreach ($langs as $lang) {
            $out['url_category'][$lang] = $this->getCategoryUrl($productWithTranslate, $lang);
            $out['url_product'][$lang] = $this->getProductUrl($productWithTranslate, $lang, $arrProduct['product_name'][$lang]);
        }

        return $out;
    }

    private function getCategoryUrl(Product $mProduct, string $lang): ?string
    {
        $mPage = $mProduct->page()->first();

        if ($mPage instanceof Page) { // phpstan fix
            return $this->urlService->getUrl($mPage, $lang);
        }

        return null; // todo - handle this case properly, maybe throw an exception or return a default URL
    }

    public function getProductUrl(Product $mProduct, string $lang, string $productName): ?string
    {
        $mPage = $mProduct->page()->first();

        if ($mPage instanceof Page) { // phpstan fix
            return $this->urlService->getUrl($mPage, $lang, Str::slug($productName, '-'));
        }

        return null; // todo - handle this case properly, maybe throw an exception or return a default URL
    }
}
