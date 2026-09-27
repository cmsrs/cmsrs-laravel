<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Tag;

use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagService
{
    /**
     * @return Collection<int, Tag>
     */
    public function getAllTags(): Collection
    {
        return Tag::with('translations')
            ->orderBy('id')
            ->get();
    }

    public function getTag(int $id): ?Tag
    {
        return Tag::with('translations')
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTag(array $data): Tag
    {
        return DB::transaction(function () use ($data) {
            $tag = Tag::create([
                'tag_category_id' => $data['tag_category_id'],
            ]);

            $this->saveTranslations(
                $tag,
                $data['name']
            );

            return $tag;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateTag(
        Tag $tag,
        array $data
    ): bool {
        return DB::transaction(function () use ($tag, $data) {
            $tag->update([
                'tag_category_id' => $data['tag_category_id'],
            ]);

            $this->saveTranslations(
                $tag,
                $data['name']
            );

            return true;
        });
    }

    public function deleteTag(Tag $tag): bool
    {
        return $tag->delete() === true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, error?: string}
     */
    public function checkIsDuplicateName(
        array $data,
        ?int $id = null
    ): array {
        foreach ($data['name'] as $lang => $name) {
            $query = TagTranslation::query()
                ->where('lang', $lang);

            if ($id !== null) {
                $query->where('tag_id', '!=', $id);
            }

            $translations = $query->get();

            foreach ($translations as $translation) {
                if (
                    Str::slug($translation->value, '-') ===
                    Str::slug($name, '-')
                ) {
                    return [
                        'success' => false,
                        'error' => "Duplicate tag: {$name} ({$lang})",
                    ];
                }
            }
        }

        return [
            'success' => true,
        ];
    }

    /**
     * @param  array<string, string>  $names
     */
    private function saveTranslations(
        Tag $tag,
        array $names
    ): void {
        foreach ($names as $lang => $value) {
            $translation = $tag->translations()
                ->where('lang', $lang)
                ->first();

            if ($translation) {
                $translation->update([
                    'value' => $value,
                ]);

                continue;
            }

            $tag->translations()->create([
                'lang' => $lang,
                'value' => $value,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toApi(Tag $tag): array
    {
        $tag->loadMissing('translations');

        $out = [
            'id' => $tag->id,
            'tag_category_id' => $tag->tag_category_id,
            'name' => [],
        ];

        foreach ($tag->translations as $translation) {
            $out['name'][$translation->lang] =
                $translation->value;
        }

        return $out;
    }
}
