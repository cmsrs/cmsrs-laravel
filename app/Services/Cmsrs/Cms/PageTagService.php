<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Cms;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Tag\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PageTagService
{
    /**
     * Get tags assigned to a page grouped by language.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getTags(Page $page): array
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
        Page $page,
        array $tagsByLang
    ): bool {
        return DB::transaction(function () use ($page, $tagsByLang) {
            DB::table('taggables')
                ->where('taggable_type', Page::class)
                ->where('taggable_id', $page->id)
                ->delete();

            foreach ($tagsByLang as $lang => $tagIds) {
                foreach ($tagIds as $tagId) {
                    $tag = Tag::find($tagId);

                    if (! $tag) {
                        continue;
                    }

                    DB::table('taggables')->insert([
                        'tag_id' => $tag->id,
                        'taggable_type' => Page::class,
                        'taggable_id' => $page->id,
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
