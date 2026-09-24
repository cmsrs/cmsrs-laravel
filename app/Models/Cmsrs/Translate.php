<?php

declare(strict_types=1);

namespace App\Models\Cmsrs;

use App\Models\Cmsrs\Cms\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $lang
 * @property string $column
 * @property string|null $value
 * @property int|null $product_id
 * @property int|null $menu_id
 * @property int|null $page_id
 * @property int|null $image_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Page|null $page
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translate whereValue($value)
 *
 * @mixin \Eloquent
 */
class Translate extends Model
{
    protected $fillable = [
        'lang',
        'column',
        'value',
        'page_id',
        'menu_id',
        'image_id',
        'product_id',
    ];

    protected $casts = [
        'page_id' => 'integer',
        'menu_id' => 'integer',
        'image_id' => 'integer',
        'product_id' => 'integer',
    ];

    /**
     * Get the page that owns the translation.
     *
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
