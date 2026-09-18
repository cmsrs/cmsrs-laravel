<?php

declare(strict_types=1);

namespace App\Models\Cmsrs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Models\Cmsrs\Tags\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

// use App\Models\Cmsrs\Product;

/**
 * @property int $id
 * @property string|null $sku
 * @property int|null $price
 * @property int $published
 * @property int|null $page_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Content> $contents
 * @property-read int|null $contents_count
 * @property-read Collection<int, Image> $images
 * @property-read int|null $images_count
 * @property-read Page|null $page
 * @property-read Collection<int, Translate> $translates
 * @property-read int|null $translates_count
 * @property-read Collection<int, Translate> $translatesPage
 * @property-read int|null $translates_page_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    /**
     * phpstan - need it, see: test_search_products_by_many_columns_pagination_docs
     * it is additional data (not related with db)
     */
    public ?string $product_name = null;

    /**
     * it is additional data (not related with db)
     */
    public ?string $page_short_title = null;

    /**
     * This is not from the database; it is calculated on the fly based on the 'price' value.
     */
    public ?string $price_description = null;

    // TODO - it is in ProductDataService
    /**
     * @var array<string>
     */
    public array $productFields = [];

    /**
     * @var array<string>
     */
    public array $columnsAllowedToSort = [
        'id',
        'published',
        'product_name', // from translate
        'page_short_title', // from translate - derive from page_id
        'sku',
        'price',
        'created_at',
        'updated_at',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'price',
        'published',
        'page_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'published' => 'integer',
        'price' => 'integer',
        'page_id' => 'integer',
    ];

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo('App\Models\Cmsrs\Page', 'page_id', 'id');
    }

    /**
     * @return HasMany<Image, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany('App\Models\Cmsrs\Image');
    }

    /**
     * @return HasMany<Translate, $this>
     */
    public function translates(): HasMany
    {
        return $this->hasMany('App\Models\Cmsrs\Translate'); // it should be work without params , 'product_id', 'id' - phpstan
    }

    /**
     * @return HasMany<Translate, $this>
     */
    public function translatesPage(): HasMany
    {
        return $this->hasMany('App\Models\Cmsrs\Translate', 'page_id', 'page_id');
    }

    /**
     * @return HasMany<Content, $this>
     */
    public function contents(): HasMany
    {
        return $this->hasMany('App\Models\Cmsrs\Content');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withPivot('lang');
    }    
}
