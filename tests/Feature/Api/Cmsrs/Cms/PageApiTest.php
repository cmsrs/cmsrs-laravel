<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Cms;

use App\Models\Cmsrs\Cms\Page;
use Tests\Feature\Api\Cmsrs\Tag\TaggableCreateApiCase;

class PageApiTest extends TaggableCreateApiCase
{
    protected function createRoute(): string
    {
        return 'api/pages';
    }

    protected function createData(array $extra = []): array
    {
        return array_merge([
            'title' => ['en' => 'Page title', 'pl' => 'Tytuł strony'],
            'short_title' => ['en' => 'Page', 'pl' => 'Strona'],
            'description' => ['en' => 'Description', 'pl' => 'Opis'],
            'published' => 0,
            'commented' => 0,
            'after_login' => 0,
            'type' => 'contact',
            'content' => ['en' => 'Content', 'pl' => 'Treść'],
            'menu_id' => '',
            'page_id' => '',
        ], $extra);
    }

    protected function taggableClass(): string
    {
        return Page::class;
    }

    protected function entityType(): string
    {
        return 'page';
    }

    protected function tableName(): string
    {
        return 'pages';
    }

    protected function idResponsePath(): string
    {
        return 'data.pageId';
    }
}
