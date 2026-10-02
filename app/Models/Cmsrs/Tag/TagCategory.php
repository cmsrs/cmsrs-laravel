<?php

namespace App\Models\Cmsrs\Tag;

use App\Enums\Cmsrs\Tag\TagCategoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagCategory extends Model
{
    protected $casts = [
        'entity_type' => TagCategoryType::class,
    ];

    /**
     * @return HasMany<Tag, $this>
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @return HasMany<TagCategoryTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TagCategoryTranslation::class);
    }
}
