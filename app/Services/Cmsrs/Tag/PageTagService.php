<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Tag;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PageTagService
{
    /**
     * Get tags assigned to a page.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getPageTags(Page $page): array
    {
        $tags = $page->tags()
            ->with('translations')
            ->orderBy('tags.id')
            ->get();

        $result = [];

        foreach ($tags as $tag) {
            $lang = $tag->pivot->lang;

            $result[$lang][] = $this->tagToArray($tag);
        }

        return $result;
    }

    /**
     * Replace all tags assigned to a page.
     *
     * @param  array<string, array<int, int>>  $tagsByLang
     */
    public function syncPageTags(Page $page, array $tagsByLang): void
    {
        DB::transaction(function () use ($page, $tagsByLang): void {
            $page->tags()->detach();

            foreach ($tagsByLang as $lang => $tagIds) {
                if ($tagIds === []) {
                    continue;
                }

                $tags = Tag::query()
                    ->whereIn('id', $tagIds)
                    ->get();

                if ($tags->count() !== count(array_unique($tagIds))) {
                    throw new \InvalidArgumentException(
                        "One or more tags do not exist for language: {$lang}"
                    );
                }

                $attach = [];

                foreach ($tags as $tag) {
                    $attach[$tag->id] = [
                        'lang' => $lang,
                    ];
                }

                $page->tags()->attach($attach);
            }
        });
    }

    /**
     * Get pages assigned to a tag.
     *
     * @return Collection<int, Page>
     */
    public function getPagesByTag(Tag $tag, ?string $lang = null): Collection
    {
        $query = $tag->pages()
            ->with([
                'translates',
                'images',
            ])
            ->orderBy('position');

        if ($lang !== null) {
            $query->wherePivot('lang', $lang);
        }

        return $query->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function tagToArray(Tag $tag): array
    {
        $translations = [];

        foreach ($tag->translations as $translation) {
            $translations[$translation->lang] = $translation->value;
        }

        return [
            'id' => $tag->id,
            'tag_category_id' => $tag->tag_category_id,
            'name' => $translations,
        ];
    }
}
