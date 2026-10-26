<?php

declare(strict_types=1);

namespace App\Http\Validation\Cmsrs;

use Illuminate\Validation\Rule;

class TagValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(string $entityType): array
    {
        return [
            'tags' => ['sometimes', 'array'],
            'tags.*' => [
                'integer',
                Rule::exists('tags', 'id')->where(function ($query) use ($entityType) {
                    $query->whereIn('tag_category_id', function ($subquery) use ($entityType) {
                        $subquery->select('id')
                            ->from('tag_categories')
                            ->where('entity_type', $entityType);
                    });
                }),
            ],
        ];
    }
}
