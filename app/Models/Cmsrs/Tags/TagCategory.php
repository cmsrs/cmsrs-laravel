<?php

namespace App\Models\Cmsrs\Tags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagCategory extends Model
{
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TagCategoryTranslation::class);
    }
}
