<?php

declare(strict_types=1);

namespace App\Services\Cmsrs;

use App\Models\Cmsrs\Shop\Checkout;
use App\Models\Cmsrs\Shop\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    /**
     * another and old approach:
     *   // $checkout = Checkout::findActiveOrder();
     *   // if(empty($checkout)){
     *   //     return false;
     *   // }
     *   //$checkout->is_pay = 1;
     *   //$checkout->save();
     */
    public function copyDataFromBasketToOrderForUser(Checkout $checkout): bool
    {

        $userId = $checkout->user_id;
        if (empty($userId)) {
            return true;
        }

        $user = User::find($userId);
        // $user = Auth::user();
        if (empty($user)) {
            return true;
        }

        /**
         * copy basket to orders form login users
         */
        $objBaskets = $checkout->baskets;
        $objOrders = Order::where('user_id', '=', $user->id)->get();  // ->toArray();

        if ($objBaskets->count()) {
            foreach ($objBaskets as $objBasket) {
                $arrBasket = $objBasket->toArray();
                $arrBasket['user_id'] = $user->id;

                unset($arrBasket['created_at']);
                unset($arrBasket['updated_at']);
                // $productId = $arrBasket['product_id'];

                // find order by product_id
                $objOrderByProductId = null;
                foreach ($objOrders as $objOrder) {
                    if ($objOrder->product_id == $objBasket->product_id) {
                        $objOrderByProductId = $objOrder;
                        break;
                    }
                }

                if (empty($objOrderByProductId)) {
                    Order::create($arrBasket);
                } else {
                    $objOrderByProductId->qty = $objOrderByProductId->qty + $objBasket->qty;
                    $objOrderByProductId->save();
                }
            }
        }

        return true;
    }

    /**
     * @return Collection<int, Order>
     */
    public function inOrdersByUserId(int $userId): Collection
    {
        return Order::where('user_id', '=', $userId)->get(); // ->toArray();
    }
}
