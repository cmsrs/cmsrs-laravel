<?php

namespace App\Models\Cmsrs\Tags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagTranslation extends Model
{
    protected $fillable = [
        'tag_id',
        'lang',
        'value',
    ];

    /**
     * TODO remove, maybe it not be necessary to define the relationship here!!!, it is useless
     * Get the tag that owns the translation.
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
