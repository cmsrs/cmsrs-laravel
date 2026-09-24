<?php

declare(strict_types=1);

namespace App\Models\Cmsrs\Interfaces;

use App\Models\Cmsrs\Translate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface TranslatableInterface
{
    /**
     * @return HasMany<Translate, Model>
     */
    public function translates(): HasMany;

    public function getId(): int;
}
