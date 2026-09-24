<?php

declare(strict_types=1);

namespace App\Models\Cmsrs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $lang
 * @property string $column
 * @property string|null $value
 * @property int|null $page_id
 * @property int|null $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereValue($value)
 *
 * @mixin \Eloquent
 */
class Content extends Model
{
    protected $fillable = [
        'lang',
        'column',
        'value',
        'page_id',
        'product_id',
    ];

    protected $casts = [
        'page_id' => 'integer',
        'product_id' => 'integer',
    ];

    public function getId(): int
    {
        return $this->id;
    }
}
