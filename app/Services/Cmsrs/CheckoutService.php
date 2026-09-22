<?php

declare(strict_types=1);

namespace App\Services\Cmsrs;

use App\Enums\Cmsrs\SortDirection;
use App\Models\Cmsrs\Shop\Basket;
use App\Models\Cmsrs\Shop\Checkout;
use App\Models\Cmsrs\Shop\Product;
use App\Services\Cmsrs\Helpers\PaginationHelperService;
use App\Services\Cmsrs\Helpers\PriceHelperService;
use App\Services\Cmsrs\Product\ProductDataService;
use App\Services\Cmsrs\Product\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CheckoutService
{
    public function __construct(private ProductService $productService, private ProductDataService $productDataService, private PriceHelperService $priceHelperService) {}

    /**
     * @return Collection<int, Checkout>
     */
    public function findActiveOrdersForUser(int $userId): Collection
    {
        return Checkout::query()
            ->where('user_id', $userId)
            ->where('is_pay', 0)
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, mixed>
     */
    public function getPaginationItems(string $lang, string $column, SortDirection $direction, ?string $search)
    {
        // $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $search = trim($search ?? '');

        $objCheckouts = Checkout::when($search, function ($query, $search) {
            return $query->where('email', 'like', '%'.$search.'%');
        })->orderBy($column, $direction->value)->get();

        // if($search){
        //     $search = '%'.$search.'%';
        //     $objCheckouts = Checkout::where('email', 'like', $search)->orderBy($column, $direction)->get();
        // }else{
        //     $objCheckouts = Checkout::orderBy($column, $direction)->get();
        // }

        $checkouts = $this->printCheckouts($objCheckouts, $lang);

        return PaginationHelperService::getPaginationFromCollection(collect($checkouts));

    }

    /**
     * @param  Collection<int, Checkout>  $checkouts
     * @return array<int, array<string, mixed>>
     */
    public function printCheckouts(Collection $checkouts, string $lang)
    {
        $out = [];
        $i = 0;
        foreach ($checkouts as $checkout) {
            $out[$i] = $this->getCheckoutItems($checkout);
            $out[$i]['baskets'] = $this->getBasketItems($checkout->baskets, $lang);
            $i++;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function getCheckoutItems(Checkout $checkout): array
    {
        $out = [];
        $out['id'] = $checkout->id;
        $out['price_total'] = $checkout->price_total;
        $out['price_total_description'] = $this->priceHelperService->getPriceDescriptionWrap((int) ($checkout->price_total ?? 0)); // phpstan8

        $out['price_deliver'] = $checkout->price_deliver;
        $out['price_deliver_description'] = $this->priceHelperService->getPriceDescriptionWrap((int) ($checkout->price_deliver ?? 0));

        $out['price_total_add_deliver'] = $checkout->price_total_add_deliver;
        $out['price_total_add_deliver_description'] = $this->priceHelperService->getPriceDescriptionWrap((int) ($checkout->price_total_add_deliver ?? 0));

        $out['user_id'] = $checkout->user_id;
        $out['email'] = $checkout->email;
        $out['first_name'] = $checkout->first_name;
        $out['last_name'] = $checkout->last_name;
        $out['address'] = $checkout->address;
        $out['country'] = $checkout->country;
        $out['city'] = $checkout->city;
        $out['telephone'] = $checkout->telephone;
        $out['postcode'] = $checkout->postcode;
        $out['is_pay'] = $checkout->is_pay;
        $out['created_at'] = $checkout->created_at;

        return $out;
    }

    /**
     * @param  Collection<int, Basket>  $baskets
     * @return array<int, array<string, mixed>>
     */
    private function getBasketItems(Collection $baskets, string $lang)
    {
        $out = [];
        $j = 0;

        // to optimization purpose
        $pIds = [];
        foreach ($baskets as $basket) {
            $pIds[$basket['product_id']] = $basket['product_id'];
        }

        $pIdsValues = array_values($pIds);
        $products = (new Product)->whereIn('id', $pIdsValues)->with(['translates'])->get()->keyBy('id'); // fix for phpstan - pluck(null, 'id')->all();

        foreach ($baskets as $basket) {
            // $product = Product::with(['translates'])->where('id', $basket['product_id'])->first(); //i don't want sql in foreach
            if (empty($product = $products[$basket['product_id']])) {
                throw new \Exception("can't find product id =".$basket['product_id']);
            }
            $productName = $this->productService->getDefaultProductName($product->translates, $lang);
            $out[$j]['qty'] = $basket->qty;

            $out[$j]['price'] = $basket->price;
            $out[$j]['price_description'] = $this->priceHelperService->getPriceDescriptionWrap((int) ($basket->price ?? 0));

            $out[$j]['product_id'] = $basket['product_id'];
            $out[$j]['product_name'] = $productName;
            $out[$j]['product_url'] = $this->productDataService->getProductUrl($product, $lang, $productName);
            $j++;
        }

        return $out;
    }
}
