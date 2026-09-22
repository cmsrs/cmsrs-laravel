<?php

declare(strict_types=1);

namespace App\Models\Cmsrs\Shop;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $address
 * @property string $country
 * @property string $city
 * @property string $telephone
 * @property string $postcode
 * @property string $session_id
 * @property bool $is_pay
 * @property int|null $price_deliver
 * @property int|null $price_total
 * @property int|null $price_total_add_deliver
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Basket> $baskets
 * @property-read int|null $baskets_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereIsPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout wherePriceDeliver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout wherePriceTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout wherePriceTotalAddDeliver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkout whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Checkout extends Model
{
    protected $fillable = [
        'email',
        'user_id',
        'first_name',
        'last_name',
        'address',
        'country',
        'city',
        'telephone',
        'postcode',
        'is_pay',
        'session_id',
        'price_total',
        'price_deliver',
        'price_total_add_deliver',
    ];

    protected $casts = [
        'is_pay' => 'boolean',
    ];

    /**
     * @var list<string>
     */
    public $columnsAllowedToSort = [
        'id',
        'price_total_add_deliver',
        'created_at',
        'updated_at',
    ];

    /**
     * @return HasMany<Basket, $this>
     */
    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
