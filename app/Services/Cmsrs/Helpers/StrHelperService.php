<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Helpers;

use Illuminate\Support\Str;

class StrHelperService
{
    public static function filterFileName(string $basename, string $extension): string
    {
        $delimiter = '-';

        $filteredName = Str::slug($basename, $delimiter);

        return $filteredName.'.'.strtolower(trim($extension));
    }

    public static function isEmpty(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }    

}
