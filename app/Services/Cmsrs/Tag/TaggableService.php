<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Tag;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Tag\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TaggableService
{
    /**
     * Get tags assigned to a page grouped by language.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getTags(Page|Product $page): array
    {
        $page->load([
            'tags.translations',
        ]);

        $out = [];

        foreach ($page->tags as $tag) {
            /** @var Tag&object{pivot: object{lang: string}} $tag */
            $lang = $tag->pivot->lang;

            if (! isset($out[$lang])) {
                $out[$lang] = [];
            }

            $out[$lang][] = $this->tagToApi($tag, $lang);
        }

        return $out;
    }

    /**
     * Replace all page tags.
     *
     * @param  array<string, array<int, int>>  $tagsByLang
     */
    public function updateTags(
        Page|Product $taggable,
        array $tagsByLang
    ): bool {
        return DB::transaction(function () use ($taggable, $tagsByLang) {
            DB::table('taggables')
                ->where('taggable_type', $taggable::class)
                ->where('taggable_id', $taggable->getId())
                ->delete();

            foreach ($tagsByLang as $lang => $tagIds) {
                foreach ($tagIds as $tagId) {
                    DB::table('taggables')->insert([
                        'tag_id' => $tagId,
                        'taggable_type' => $taggable::class,
                        'taggable_id' => $taggable->getId(),
                        'lang' => $lang,
                    ]);
                }
            }

            return true;
        });
    }

    /**
     * Get pages assigned to a tag.
     *
     * @return Collection<int, Page>
     */
    public function getPagesByTag(Tag $tag): Collection
    {
        return $tag->pages()
            ->withPivot('lang')
            ->with([
                'translates',
            ])
            ->orderBy('pages.id')
            ->get();
    }

    /**
     * Get products assigned to a tag.
     *
     * @return Collection<int, Product>
     */
    public function getProductsByTag(Tag $tag): Collection
    {
        return $tag->products()
            ->withPivot('lang')
            ->with([
                'translates',
            ])
            ->orderBy('products.id')
            ->get();
    }

    /**
     * Convert tag to API structure.
     *
     * @return array<string, mixed>
     */
    private function tagToApi(Tag $tag, string $lang): array
    {
        $translation = $tag->translations
            ->firstWhere('lang', $lang);

        return [
            'id' => $tag->id,
            'name' => $translation?->value,
            'lang' => $lang,
        ];
    }
}
