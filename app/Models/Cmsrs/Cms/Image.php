<?php

declare(strict_types=1);

namespace App\Models\Cmsrs\Cms;

use App\Models\Cmsrs\Interfaces\TranslatableInterface;
use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Traits\HasTranslationsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $name
 * @property int|null $position
 * @property int|null $page_id
 * @property int|null $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Translate> $translates
 * @property-read int|null $translates_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Image extends Model implements TranslatableInterface
{
    use HasTranslationsTrait;

    const IMAGE_DIR = 'images';

    const IMAGE_ORG = 'org';

    const IMAGE_THUMB_TYPE_SMALL = 'small';

    const IMAGE_THUMB_TYPE_MEDIUM = 'medium';

    /**
     * @var array<string, array<string, int>>
     */
    public static $thumbs = [
        self::IMAGE_THUMB_TYPE_SMALL => [
            'x' => 100,
            'y' => 100,
        ],
        self::IMAGE_THUMB_TYPE_MEDIUM => [
            'x' => 300,
            'y' => 300,
        ],
    ];

    protected $fillable = [
        'name',
        'position',
        'page_id',
        'product_id',
    ];

    /**
     * @var array<string, string>
     */
    public static $type = [
        'page' => 'page_id',
        'product' => 'product_id',
    ];

    /**
     * @return array<string, string>
     */
    public static $modelClassesByType = [
        'page' => Page::class,
        'product' => Product::class,
    ];

    protected $casts = [
        'page_id' => 'integer',
        'product_id' => 'integer',
        'position' => 'integer',
    ];

    /**
     * @return HasMany<Translate, $this>
     */
    public function translates(): HasMany
    {
        return $this->hasMany(Translate::class, 'image_id', 'id');
    }

    public function getId(): int
    {
        return $this->id;
    }
}
