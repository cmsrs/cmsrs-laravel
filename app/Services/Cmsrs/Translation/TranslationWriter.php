<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Translation;

use App\Models\Cmsrs\Content;
use App\Models\Cmsrs\Translate;
use App\Services\Cmsrs\ConfigService;

class TranslationWriter
{
    public function __construct(private ConfigService $configService) {}

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
            if ($require && empty($data[$column])) {
                throw new \Exception("Translation problem, require column: $column, var= ".var_export($data, true));
            }
            $col = ! empty($data[$column]) ? $data[$column] : []; // $col = $data[$column] ?? [];
            foreach ($this->configService->arrGetLangs() as $lang) {
                $value = ! empty($col[$lang]) ? $col[$lang] : null; // $value = $col[$lang] ?? null;

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

        return true;
    }
}
