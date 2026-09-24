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
}
