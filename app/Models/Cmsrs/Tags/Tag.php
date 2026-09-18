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

    public function category(): BelongsTo
    {
        return $this->belongsTo(TagCategory::class, 'tag_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class);
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'taggable')->withPivot('lang');
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable')->withPivot('lang');
    }
}
