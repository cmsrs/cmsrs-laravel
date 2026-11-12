<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cmsrs\Cms;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\Translation\TranslationReader;
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

    public function test_page_translations_default_and_update_independently(): void
    {
        $response = $this->post($this->createRoute().'?token='.$this->token, $this->createData());
        $response->assertJsonPath('success', true);
        $pageId = $response->json($this->idResponsePath());
        $page = Page::findOrFail($pageId);
        $reader = app(TranslationReader::class);

        $this->assertSame('Page title', $reader->translatesByColumnAndLang($page, 'h1', 'en'));
        $this->assertSame('page-title', $reader->translatesByColumnAndLang($page, 'url_slug', 'en'));
        $this->assertSame('Tytuł strony', $reader->translatesByColumnAndLang($page, 'h1', 'pl'));
        $this->assertSame('tytul-strony', $reader->translatesByColumnAndLang($page, 'url_slug', 'pl'));

        $list = $this->get('api/pages?token='.$this->token)->assertJsonPath('success', true);
        $this->assertSame('Page title', $list->json('data.0.h1.en'));
        $this->assertSame('page-title', $list->json('data.0.url_slug.en'));
        $detail = $this->get('api/pages/'.$pageId.'?token='.$this->token)->assertJsonPath('success', true);
        $this->assertSame('Page title', $detail->json('data.h1.en'));
        $this->assertSame('page-title', $detail->json('data.url_slug.en'));
        $byType = $this->get('api/pages/type/contact?token='.$this->token)->assertJsonPath('success', true);
        $this->assertSame('Page title', $byType->json('data.0.h1.en'));
        $this->assertSame('page-title', $byType->json('data.0.url_slug.en'));

        $responseWrong = $this->put('api/pages/'.$pageId.'?token='.$this->token, [
            'title' => ['en' => 'Updated title', 'pl' => 'Zaktualizowany tytul'],
        ]);

        $responseWrong->assertJsonPath('success', false);
        $responseWrong->assertJsonPath('error', 'Invalid url_slug:  (en)');

        $response = $this->put('api/pages/'.$pageId.'?token='.$this->token, [
            'title' => ['en' => 'Updated title', 'pl' => 'Zaktualizowany tytul'],
            'url_slug' => ['en' => 'custom-address', 'pl' => 'niestandardowy-adres'],
            'short_title' => ['en' => 'Page', 'pl' => 'Strona'],
            'h1' => ['en' => 'test123', 'pl' => ''],
        ]);

        //$d = $response->getData(); dd($d);
        $response->assertJsonPath('success', true);

        $this->assertSame('custom-address', $reader->translatesByColumnAndLang($page, 'url_slug', 'en'));
        $this->assertSame('test123', $reader->translatesByColumnAndLang($page, 'h1', 'en'));
        $this->assertSame(null, $reader->translatesByColumnAndLang($page, 'h1', 'pl'));
    }

    public function test_page_creation_the_same_url_slug(): void
    {
        $data = $this->createData([
            'title' => ['en' => 'Unique!', 'pl' => 'Unikalny'],
            'url_slug' => ['en' => 'different', 'pl' => 'witaj-strona'],
        ]);
        $first = $this->post($this->createRoute().'?token='.$this->token, $data);
        $first->assertJsonPath('success', true);
        $page1 = Page::findOrFail($first->json($this->idResponsePath()));
        $reader = app(TranslationReader::class);
        $this->assertSame('different', $reader->translatesByColumnAndLang($page1, 'url_slug', 'en'));

        $second = $this->post($this->createRoute().'?token='.$this->token, $this->createData([
            'title' => ['en' => 'Different', 'pl' => 'Inny tytuł'],
            'url_slug' => ['en' => 'different'],
        ]));
        $second->assertJsonPath('success', false);
        // $res = $second->getData(); dd( $res );
        $second->assertJsonPath('error', 'Duplicate url_slug: different (en)');
    }

    public function test_page_creation_keeps_supplied_multilingual_h1_and_slug_and_rejects_duplicate_slugs(): void
    {
        $data = $this->createData([
            'title' => ['en' => 'Unique!', 'pl' => 'Unikalny'],
            'h1' => ['en' => 'Welcome', 'pl' => 'Witaj'],
            'url_slug' => ['en' => 'different', 'pl' => 'witaj-strona'],
        ]);
        $first = $this->post($this->createRoute().'?token='.$this->token, $data);
        $first->assertJsonPath('success', true);
        $page1 = Page::findOrFail($first->json($this->idResponsePath()));
        $reader = app(TranslationReader::class);
        $this->assertSame('Welcome', $reader->translatesByColumnAndLang($page1, 'h1', 'en'));
        $this->assertSame('different', $reader->translatesByColumnAndLang($page1, 'url_slug', 'en'));

        $second0 = $this->post($this->createRoute().'?token='.$this->token, $this->createData([
            'title' => ['en' => 'unique!', 'pl' => 'Inny tytuł'],
        ]));
        $second0->assertJsonPath('success', false);
        $second0->assertJsonPath('error', 'Duplicate title: Unique! (en)');
        // $res = $second0->getData(); dd( $res );

        $second = $this->post($this->createRoute().'?token='.$this->token, $this->createData([
            'title' => ['en' => 'Different', 'pl' => 'Inny tytuł'],
            'h1' => ['en' => 'Welcome', 'pl' => 'Witaj'],
        ]));
        $second->assertJsonPath('success', true);
        $page2 = Page::findOrFail($second->json($this->idResponsePath()));
        $this->assertSame('different-2', $reader->translatesByColumnAndLang($page2, 'url_slug', 'en'));

        $third = $this->post($this->createRoute().'?token='.$this->token, $this->createData([
            'title' => ['en' => 'Yet another', 'pl' => 'Jeszcze inny'],
            'url_slug' => ['en' => 'different'],
        ]));
        $third->assertJsonPath('success', false);
        // $res = $third->getData(); dd( $res );
        $third->assertJsonPath('error', 'Duplicate url_slug: different (en)');
        $this->assertDatabaseCount('pages', 2);

        $four = $this->post($this->createRoute().'?token='.$this->token, $this->createData([
            'title' => ['en' => 'Yet another', 'pl' => 'Jeszcze inny'],
            'url_slug' => ['pl' => 'different'], // pl!
        ]));
        $four->assertJsonPath('success', true);
        $this->assertDatabaseCount('pages', 3);
    }
}
