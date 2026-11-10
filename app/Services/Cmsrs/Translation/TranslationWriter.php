<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Translation;

use App\Models\Cmsrs\Content;
use App\Models\Cmsrs\Translate;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\CacheManagerService;

class TranslationWriter
{
    public function __construct(private ConfigService $configService, private CacheManagerService $cacheManagerService) {}

    /**
     * @param  array<string, mixed>  $d
     * @param  array<string, bool>  $columns
     *
     * @throws \Exception
     */
    public function genericCreateTranslate(array $d, string $refName, array $columns, string $modelClass, bool $create = true): bool
    {
        $refId = $d[$refName];
        $data = $d['data'];
        foreach ($columns as $column => $require) {
            if (! $create && ! array_key_exists($column, $data)) { // problem with h1, url_slug
                continue;
            }

            if ($require && empty($data[$column])) {
                throw new \Exception("Translation problem, require column: $column, var= ".var_export($data, true));
            }
            $col = (isset($data[$column]) && is_array($data[$column])) ? $data[$column] : [];
            foreach ($this->configService->arrGetLangs() as $lang) {
                if (! $create && ! array_key_exists($lang, $col)) { // problem with h1, url_slug
                    continue;
                }
                if (in_array($column, ['h1', 'url_slug'], true)) { // problem with h1, url_slug
                    $value = array_key_exists($lang, $col) ? $col[$lang] : null;
                } else { // prblem with h1, url_slug
                    // Preserve legacy behavior for existing translation fields.
                    $value = ! empty($col[$lang]) ? $col[$lang] : null;
                }

                if ($require && ! $value) {
                    throw new \Exception("Translation problem, require lang: $lang for column: $column, var= ".var_export($data, true));
                }

                $row = [$refName => $refId, 'column' => $column, 'lang' => $lang, 'value' => $value];
                if ($create) {
                    $this->createRow($row, $modelClass);
                } else {
                    $this->updateRow($row, $modelClass);
                }
            }

        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function createRow(array $row, string $modelClass): void
    {
        /** @var Content|Translate $model */
        $model = new $modelClass;
        $created = $model->create($row);
        if (! $created->getId()) {
            throw new \Exception("Problem with save into {$modelClass}");
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function updateRow(array $row, string $modelClass): bool
    {
        $query = $modelClass::query()
            ->where('column', $row['column'])
            ->where('lang', $row['lang']);

        foreach (['page_id', 'product_id', 'menu_id', 'image_id'] as $key) {
            if (! empty($row[$key])) {
                $query->where($key, $row[$key]);
                break;
            }
        }

        $obj = $query->first();
        if ($obj === null) {
            $this->createRow($row, $modelClass);
        } else {
            $obj->update(['value' => $row['value']]);
        }

        // TODO: cache clear -  required tests
        // $modelName = strtolower(class_basename($modelClass));
        // $modelId = $row['page_id'] ?? $row['menu_id'] ?? $row['image_id'] ?? $row['product_id'] ?? null;
        // if ($modelId !== null) {
        //     cache()->forget($this->cacheManagerService->key($modelName.'_translate', $row['column'].'_'.$modelId, $row['lang']));
        // }

        return true;
    }
}
