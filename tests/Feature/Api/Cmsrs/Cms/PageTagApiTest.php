<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Cms;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Api\Cmsrs\Tag\TaggableApiCase;

class PageTagApiTest extends TaggableApiCase
{
    protected function createTaggable(): Model
    {
        return Page::create([
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'position' => 1,
            'type' => 'cms',
        ]);
    }

    protected function taggableRoute(Model $model): string
    {
        return 'api/pages/'.$model->id.'/tags';
    }

    protected function byTagRoute(Tag $tag): string
    {
        return 'api/pages/tag/'.$tag->id;
    }

    protected function fakeTaggableRoute(): string
    {
        return 'api/pages/99999/tags';
    }

    protected function byTagRouteForId(int $id): string
    {
        return 'api/pages/tag/'.$id;
    }

    protected function taggableClass(): string
    {
        return Page::class;
    }
}
