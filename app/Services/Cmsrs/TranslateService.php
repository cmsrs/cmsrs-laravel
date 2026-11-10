<?php

declare(strict_types=1);

namespace App\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Menu;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Translate;
use App\Services\Cmsrs\Translation\TranslationWriter;

class TranslateService
{
    public function __construct(
        private ConfigService $configService,
        private TranslationWriter $translationWriter,
    ) {}

    /**
     * @return array<int, string>
     *                            use in tests
     */
    public function getArrLangs(): array
    {
        return $this->configService->arrGetLangs();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function wrapCreate(array $data, bool $create = true): bool
    {
        // Empty values in existing translation fields have historically been stored as null.
        // Page headings and slugs keep their explicit value, including an empty heading.
        //$data = $this->normalizeEmptyValues($data, ['h1', 'url_slug']);

        if (! empty($data['menu_id'])) {
            $requiredColumn = (new Menu)->requiredColumn;
            $columns = ['name' => in_array('name', $requiredColumn)];
            $this->translationWriter->genericCreateTranslate($data, 'menu_id', $columns, Translate::class, $create);
        } elseif (! empty($data['page_id'])) {
            $requiredColumn = (new Page)->requiredColumn;
            $columns = [
                'title' => in_array('title', $requiredColumn),
                'short_title' => in_array('short_title', $requiredColumn),
                'description' => in_array('description', $requiredColumn),
                'h1' => false,
                'url_slug' => false,
            ];
            $this->translationWriter->genericCreateTranslate($data, 'page_id', $columns, Translate::class, $create);
        } elseif (! empty($data['image_id'])) {
            $columns = ['alt' => false];
            $this->translationWriter->genericCreateTranslate($data, 'image_id', $columns, Translate::class, $create);
        } elseif (! empty($data['product_id'])) {
            $columns = [
                'product_name' => true,
            ];
            $this->translationWriter->genericCreateTranslate($data, 'product_id', $columns, Translate::class, $create);
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $preserveEmptyColumns
     * @return array<string, mixed>
     */
    // private function normalizeEmptyValues(array $data, array $preserveEmptyColumns = []): array
    // {
    //     foreach (($data['data'] ?? []) as $column => $translations) {
    //         if (! is_array($translations) || in_array($column, $preserveEmptyColumns, true)) {
    //             continue;
    //         }

    //         foreach ($translations as $lang => $value) {
    //             if (empty($value)) {
    //                 $data['data'][$column][$lang] = '';
    //             }
    //         }
    //     }

    //     return $data;
    // }
}
