<?php

declare(strict_types=1);

namespace App\Models\Cmsrs\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $qty
 * @property int $checkout_id
 * @property int|null $price
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket whereCheckoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Basket whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Basket extends Model
{
    protected $fillable = [
        'qty',
        'price',
        'checkout_id',
        'product_id',
    ];

    protected $casts = [
        'price' => 'integer',
        'checkout_id' => 'integer',
        'product_id' => 'integer',
    ];
}
