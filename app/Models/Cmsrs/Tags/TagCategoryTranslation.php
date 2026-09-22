<?php

namespace App\Models\Cmsrs\Tags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagCategoryTranslation extends Model
{
    protected $fillable = [
        'tag_category_id',
        'lang',
        'value',
    ];

    /**
     * TODO remove, maybe it not be necessary to define the relationship here!!!, it is useless
     * Get the category that owns the translation.
     */

    /**
     * @return BelongsTo<TagCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TagCategory::class, 'tag_category_id');
    }
}
