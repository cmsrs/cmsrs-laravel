<?php

declare(strict_types=1);

namespace App\Models\Cmsrs\Traits;

use App\Models\Cmsrs\Translate;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslationsTrait
{
    public function translates(): HasMany
    {
        /** @var self $this */
        return $this->hasMany(Translate::class);
    }
}
