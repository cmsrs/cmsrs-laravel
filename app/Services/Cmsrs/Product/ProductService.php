<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Product;

use App\Enums\Cmsrs\SortDirection;
use App\Models\Cmsrs\Cms\Translate;
use App\Models\Cmsrs\Shop\Basket;
use App\Models\Cmsrs\Shop\Checkout;
use App\Models\Cmsrs\Shop\Product;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\ContentService;
use App\Services\Cmsrs\DeliverService;
use App\Services\Cmsrs\Helpers\ArrObjHelperService;
use App\Services\Cmsrs\Helpers\PaginationHelperService;
use App\Services\Cmsrs\Helpers\PriceHelperService;
use App\Services\Cmsrs\ImageService;
use App\Services\Cmsrs\PaymentService;
use App\Services\Cmsrs\TranslateService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private ImageService $imageService,
        private PriceHelperService $priceHelperService,
        private DeliverService $deliverService,
        private TranslateService $translateService,
        private ContentService $contentService,
        private ProductDataService $productDataService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getProductData(Product $product, string $lang): array
    {
        $data = [];
        $urls = $this->productDataService->getProductUrls($product);
        $data['url_category'] = $urls['url_category'];
        // $data['url_product'] = $urls['url_product'];
        $productArr = $this->productDataService->getProductDataByProductArr($product);
        $data['product'] = $productArr;
        $data['h1'] = $productArr['product_name'][$lang];
        $data['product_name'] = $productArr['product_name'];
        $data['product_name_slug'] = $productArr['product_name_slug'];
        $data['page_title'] = $productArr['product_name'][$lang] ?? config('app.name', 'cmsRS');
        $data['seo_description'] = $productArr['product_description'][$lang] ?? config('app.name', 'cmsRS');

        $data['images'] = $this->imageService->getImagesAndThumbsByTypeAndRefId('product', $product->id, $lang);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function saveCheckout(array $data, ?int $userId, ?string $sessionId): array
    {
        if (empty($data['products']) || ! is_array($data['products'])) {
            throw new \Exception('No products in post - checkout');
        }
        $payment = PaymentService::getPayment($data['payment']);
        if (empty($payment)) {
            throw new \Exception('Payment problem - checkout');
        }

        $deliver = $this->deliverService->getDeliver($data['deliver']);
        if (empty($deliver)) {
            throw new \Exception('Deliver problem - checkout');
        }

        $reindexBaskets = ArrObjHelperService::reIndexArr($data['products']);
        $productsDataAndTotalAmount = $this->createPaymentData($reindexBaskets);
        if (empty($productsDataAndTotalAmount['baskets'])) {
            throw new \Exception('No data in basket (not found data in db)');
        }
        $baskets = $productsDataAndTotalAmount['baskets'];

        unset($data['products']);
        $checkout = $data;
        $checkout['user_id'] = $userId; // Auth::check() ? Auth::user()->id : null;
        $checkout['session_id'] = $sessionId; // session()->getId();
        $checkout['price_total'] = $productsDataAndTotalAmount['totalAmount'];
        $checkout['price_deliver'] = $deliver['price'];
        $checkout['price_total_add_deliver'] = $checkout['price_total'] + $checkout['price_deliver'];

        // without transaction
        // $objCheckout = Checkout::create($checkout);
        // if (empty($objCheckout->id)) {
        //     throw new \Exception("I cant get objCheckout id - problem with save checkout");
        // }

        // foreach($baskets as $basket){
        //     $basket['checkout_id'] = $objCheckout->id;
        //     Basket::create($basket);
        //     Log::debug(' create basket: '.var_export( $basket , true ) );
        // }

        DB::beginTransaction();
        try {
            $objCheckout = Checkout::create($checkout);
            if (empty($objCheckout->id)) {
                throw new \Exception('I cant get objCheckout id - problem with save checkout');
            }

            foreach ($baskets as $basket) {
                $basket['checkout_id'] = $objCheckout->id;
                Basket::create($basket);
                // Log::debug(' create basket: '.var_export( $basket , true ) );
            }
            DB::commit();
        } catch (\Exception $e) {
            Log::debug(' transaction problem: '.var_export($e->getMessage(), true));
            DB::rollback();
            // throw $e;
        }

        return [
            'productsDataAndTotalAmount' => $productsDataAndTotalAmount,
            'checkout' => $checkout,
            'objCheckout' => $objCheckout,
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function getPaginationItems(string $lang, string $column, SortDirection $direction, ?string $search): LengthAwarePaginator
    {
        $products = (new Product)->with(['translates' => function ($query) use ($lang) {
            $query->where('lang', $lang)->where('column', 'product_name');
        }])->with(['translatesPage' => function ($query) use ($lang) {
            $query->where('lang', $lang)->where('column', 'short_title');
        }])
            ->get();

        $products->each(function ($product) {
            $firstTranslation = $product->translates->first();
            $productName = $firstTranslation instanceof Translate ? $firstTranslation->value : null;
            $product->setAttribute('product_name', $productName);
            unset($product['translates']);

            $firstTranslationPage = $product->translatesPage->first();
            $pageShortTitle = $firstTranslationPage instanceof Translate ? $firstTranslationPage->value : null;
            $product->setAttribute('page_short_title', $pageShortTitle);
            unset($product['translatesPage']);

            $priceDescription = $this->priceHelperService->getPriceDescriptionWrap($product->price ?? 0);
            $product->setAttribute('price_description', $priceDescription);
        });

        if ($search) {
            $search = trim($search);
            $products = $products->filter(function ($product) use ($search) {
                $productName = $product->getAttribute('product_name');
                $productNameContainsSearch = $productName !== null && str_contains(trim($productName), $search);
                $skuContainsSearch = $product->sku !== null && str_contains(trim($product->sku), $search);

                return $productNameContainsSearch || $skuContainsSearch;
            });
            $products = $products->values(); // reset keys - start from 0
        }

        // $products = ($direction == 'desc') ? $products->sortByDesc($column) : $products->sortBy($column);
        $isDesc = $direction === SortDirection::DESC;

        $products = $isDesc
            ? $products->sortByDesc($column)
            : $products->sortBy($column);

        $productsPagination = PaginationHelperService::getPaginationFromCollection($products->values()); // values() - reset keys

        // For optimization purposes, we only retrieve images for products on the given page.
        $productsPagination->each(function ($product) {
            $product->images = $this->imageService->getImagesAndThumbsByTypeAndRefId('product', $product->id);
        });

        return $productsPagination;
    }

    /**
     * @param  array<int, mixed>  $obj
     * @return array<int, mixed>
     */
    public static function objToArray(array $obj): array
    {
        $out = [];
        foreach ($obj as $o) {
            $out[] = $o->product_id;
        }

        return $out;
    }

    /**
     * @return array<int, mixed>
     */
    public function searchProducts(string $lang, ?string $key): array
    {
        return DB::select("select distinct product_id from translates where (`product_id` is not null) and (`lang` = :lang) and (`column` = 'product_name') and (`value` like  :key )", ['lang' => $lang, 'key' => '%'.$key.'%']);
    }

    /**
     * @return array<int, mixed>
     */
    public function wrapSearchProducts(string $lang, ?string $key): array
    {
        $objProducts = $this->searchProducts($lang, $key);
        $arrProducts = ProductService::objToArray($objProducts);

        return $this->getProductsWithImagesByIds($arrProducts);
    }

    /**
     * @param  Collection<int, Translate>  $productTranslates
     */
    public static function getDefaultProductName(Collection $productTranslates, string $lang): string
    {
        $defaultProductName = '';

        foreach ($productTranslates as $translate) {
            if (($translate->column == 'product_name') && ($translate->lang == $lang)) {
                $defaultProductName = $translate->value ?? '';
                break;
            }
        }

        return $defaultProductName;
    }

    /**
     * @param  array<int, mixed>  $arrCart
     * @return array<string, mixed>
     */
    public function createPaymentData(array $arrCart): array
    {
        $ids = array_keys($arrCart);

        $products = Product::with(['translates'])
            ->whereIn('id', $ids)
            ->orderBy('id', 'asc')
            ->get();

        $lang = ConfigService::getDefaultLang();

        $result = [
            'products' => [],
            'baskets' => [],
            'orders' => [],
            'totalAmount' => 0,
        ];

        foreach ($products as $product) {

            $item = $arrCart[$product->id];

            if (empty($item['qty'])) {
                throw new \Exception('qty empty - something wrong');
            }

            $qty = $item['qty'];

            $name = ProductService::getDefaultProductName(
                $product->translates,
                $lang
            );

            // 1. PAYMENT PRODUCTS
            $result['products'][] = [
                'name' => $name,
                'unitPrice' => $product->price,
                'quantity' => $qty,
            ];

            // 2. BASKETS
            $result['baskets'][] = [
                'qty' => $qty,
                'price' => $product->price,
                'product_id' => $product->id,
            ];

            // 3. ORDERS
            $productImage = $this->imageService
                ->getImagesAndThumbsByTypeAndRefId('product', $product->id)
                ->toArray();

            $result['orders'][] = [
                'name' => $name,
                'unitPrice' => $product->price,
                'unit_price_description' => $this->priceHelperService->getPriceDescriptionWrap($product->price ?? 0),
                'qty' => $qty,
                'product_id' => $product->id,
                'product_url' => $this->productDataService->getProductUrl($product, $lang, $name),
                'product_img' => $productImage[0]['fs']['small'] ?? '',
            ];

            $result['totalAmount'] += $product->price * $qty;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function checkIsDuplicateName(array $data, ?int $id = null): array
    {
        $out = ['success' => true];
        $products = $this->productDataService->getAllProductsWithImages();
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                continue;
            }
            foreach ($product['product_name'] as $lang => $name) {
                if (empty($data['product_name']) || empty($data['product_name'][$lang])) {
                    throw new \Exception('product_name is empty - but is require');
                }
                $nameIn = Str::slug($data['product_name'][$lang], '-');
                $n = Str::slug($name, '-');

                if ($nameIn == $n) {
                    $out['success'] = false;
                    $out['error']['product_name'] = "Duplicate product name: $name ($lang)";
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * use also in script to load demo (test) data
     * php artisan cmsrs:load-demo-data
     *
     * @param  array<string, mixed>  $data
     */
    public function wrapCreate(array $data): Product
    {
        $product = Product::create($data);

        if (empty($product->id)) {
            throw new \Exception('I cant get product id');
        }
        $this->createTranslate(['product_id' => $product->id, 'data' => $data]);

        if (! empty($data['images']) && is_array($data['images'])) {
            // $this->imageService->setTranslate($this->translateService);
            $this->imageService->createImages($data['images'], 'product', $product->id);
        }

        return $product;
    }

    /**
     * @param  array<string, mixed>  $dd
     */
    public function createTranslate(array $dd, bool $create = true): void
    {
        $this->translateService->wrapCreate($dd, $create);
        $this->contentService->wrapCreate($dd, $create);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function wrapUpdate(Product $mProduct, array $data): bool
    {
        $res = $mProduct->update($data);
        $this->createTranslate(['product_id' => $mProduct->id, 'data' => $data], false);

        return $res;
    }

    public function getProductBySlug(string $productSlug, string $lang): ?Product
    {
        $productOut = null;
        $products = $this->productDataService->getAllProductsWithTranslates();
        foreach ($products as $product) {
            $arrProduct = $this->productDataService->getProductDataFormat($product);
            if ($productSlug == Str::slug($arrProduct['product_name'][$lang], '-')) {
                $productOut = $product;
                break;
            }
        }

        return $productOut;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductWithTranslatesContentsAndImages(Product $mProduct): array
    {
        $productOut = Product::with(['translates', 'contents'])->find($mProduct->id);
        if (! $productOut) { // phpstan8 fix
            throw new \Exception('Product not found');
        }

        return $this->productDataService->getProductDataByProductArr($productOut);
    }

    /**
     * It is needed for sitemap
     *
     * @return array<int, array<string, string|null>>
     */
    public function getProductsUrl(): array
    {
        $urls = [];
        $products = Product::with(['translates', 'contents', 'page'])->where('published', '=', 1)->orderBy('id', 'asc')->get();
        $i = 0;
        foreach ($products as $product) {
            if ($product['page']->published && $product->published) {
                $arrProduct = $this->productDataService->getProductDataFormat($product);
                $langs = ConfigService::arrGetLangsEnv();
                foreach ($langs as $lang) {
                    $urls[$i][$lang] = $this->productDataService->getProductUrl($product, $lang, $arrProduct['product_name'][$lang]);
                }
                $i++;
            }
        }

        return $urls;
    }

    /**
     * it is needed to search
     *
     * @param  array<int, int>  $ids
     * @return array<int, mixed>
     */
    public function getProductsWithImagesByIds(array $ids): array
    {
        $products = Product::with(['translates', 'contents'])->whereIn('id', $ids)->orderBy('id', 'asc')->where('published', '=', 1)->get();

        return $this->productDataService->dataToRender($products);
    }
}
