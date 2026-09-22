<?php

namespace App\Models\Cmsrs\Tags;

use App\Models\Cmsrs\Page;
use App\Models\Cmsrs\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = [
        'tag_category_id',
    ];

    /**
     * @return BelongsTo<TagCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TagCategory::class, 'tag_category_id');
    }

    /**
     * @return HasMany<TagTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class);
    }

    /**
     * @return MorphToMany<Page, $this>
     */
    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'taggable')->withPivot('lang');
    }

    /**
     * @return MorphToMany<Product, $this>
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable')->withPivot('lang');
    }
}
