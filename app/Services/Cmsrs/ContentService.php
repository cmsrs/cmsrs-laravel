<?php

declare(strict_types=1);

namespace App\Services\Cmsrs;

use App\Models\Cmsrs\Content;
use App\Services\Cmsrs\Translation\TranslationWriter;

class ContentService
{
    public function __construct(
        private TranslationWriter $translationWriter,
    ) {}

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'lang',
        'column',
        'value',
        'page_id',
        'product_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'page_id' => 'integer',
        'product_id' => 'integer',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function wrapCreate(array $data, bool $create = true): bool
    {
        if (! empty($data['page_id'])) {
            $columns = [
                'content' => false,
            ];
            $this->translationWriter->genericCreateTranslate($data, 'page_id', $columns, Content::class, $create);
        } elseif (! empty($data['product_id'])) {
            $columns = [
                'product_description' => false,
            ];
            $this->translationWriter->genericCreateTranslate($data, 'product_id', $columns, Content::class, $create);
        }

        return true;
    }
}
