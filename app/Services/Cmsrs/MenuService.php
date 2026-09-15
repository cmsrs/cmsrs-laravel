<?php

declare(strict_types=1);

namespace App\Services\Cmsrs;

use App\Models\Cmsrs\Menu;
use App\Models\Cmsrs\Page;
use App\Services\Cmsrs\Helpers\CacheManagerService;
use App\Services\Cmsrs\Translation\TranslationReader;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Throwable;

class MenuService
{
    public function __construct(
        private CacheManagerService $cacheManagerService,
        private TranslateService $translateService,
        private TranslationReader $translationReader
    ) {}

    /**
     * @return Collection<int, Menu>
     */
    public function getMenu(): Collection
    {
        $key = $this->cacheManagerService->key(
            'menus',
            'all'
        );

        return $this->cacheManagerService->remember(
            $key,
            fn () => $this->getMenuPriv()
        );
    }

    /**
     * use in cli command.
     */
    public function isWelcomePage(): bool
    {
        return $this->countPublishedPages() === 0 && $this->countItemsInMenu() === 0;
    }

    private function countPublishedPages(): int
    {
        return Page::where('published', 1)->count();
    }

    private function countItemsInMenu(): int
    {
        return Menu::count();
    }

    /**
     * @return Collection<int, Menu>
     */
    private function getMenuPriv(): Collection
    {
        return Menu::orderBy('position')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function createMenu(array $data): Menu
    {
        $data['position'] = $this->getNextPosition();

        $menu = Menu::create($data);
        if (empty($menu->id)) {
            throw new \Exception('I cant get menu id');
        }

        return $menu;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function wrapUpdate(Menu $mMenu, array $data): bool
    {
        $mMenu->update($data);
        $this->translateService->wrapCreate(['menu_id' => $mMenu->id, 'data' => $data], false);

        return true;
    }

    /**
     * use also in script to load demo (test) data
     * php artisan cmsrs:load-demo-data
     *
     * @param  array<string, mixed>  $data
     */
    public function wrapCreate(array $data): Menu
    {
        $menu = $this->createMenu($data);
        $this->translateService->wrapCreate(['menu_id' => $menu->id, 'data' => $data], true);

        return $menu;
    }

    public function getSlugByLang(Menu $model, string $lang): string
    {
        $column = 'name';
        $name = $this->translationReader->translatesByColumnAndLang($model, $column, $lang);

        if (! is_string($name) || $name === '') {
            throw new \RuntimeException("Missing translation for menu name in lang: $lang");
        }

        return Str::slug($name, '-');
    }

    /**
     * @return Collection<int, Page>
     */
    public function pagesPublished(Menu $mMenu): Collection
    {
        $pages = $mMenu->pages()->where('published', '=', 1)->orderBy('position', 'asc')->get();

        return $pages;
    }

    /**
     * @return Collection<int, Page>
     */
    public function pagesPublishedAndAccess(Menu $menu, bool $isAuthenticated): Collection
    {
        $query = $menu->pages()
            ->where('published', 1);

        if (! $isAuthenticated) {
            $query->where('after_login', 0);
        }

        return $query->orderBy('position')->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllMenus(): array
    {
        $menus = Menu::with('translates')->orderBy('position', 'asc')->get()->toArray();

        $out = [];
        $i = 0;
        foreach ($menus as $menu) {
            $out[$i]['id'] = $menu['id'];
            $out[$i]['position'] = $menu['position'];
            foreach ($menu['translates'] as $translate) {
                $out[$i][$translate['column']][$translate['lang']] = $translate['value'];
            }
            $i++;
        }

        return $out;
    }

    /**
     * @param  array<string, array<string, string>>  $data
     * @return array<string, mixed>
     */
    public function checkIsDuplicateName(array $data, ?int $id = null): array
    {
        $out = ['success' => true];
        $menus = $this->getAllMenus();
        foreach ($menus as $menu) {
            if ($menu['id'] == $id) {
                continue;
            }
            foreach ($menu['name'] as $lang => $name) {
                if (empty($data['name']) || empty($data['name'][$lang])) {
                    throw new \Exception('menu name is empty - but is require');
                }
                $nameIn = Str::slug($data['name'][$lang], '-');
                $n = Str::slug($name, '-');
                if ($nameIn == $n) {
                    $out['success'] = false;
                    $out['error'] = "Duplicate menu: $name ($lang)";
                    break;
                }
            }
        }

        return $out;
    }

    public function getNextPosition(): int
    {
        $menu = Menu::query()
            ->orderBy('position', 'desc')
            ->first();

        if (! $menu) {
            return 1;
        }

        return $menu->position + 1;
    }

    public function swapPosition(string $direction, int $id): bool
    {
        if (! in_array($direction, ['up', 'down'])) {
            throw new \Exception('Wrong direction (Menu). It can be up or down direction = '.$direction);
        }

        $menus = Menu::query()
            ->orderBy('position', 'asc')
            ->get();

        $countMenus = count($menus);
        if ($countMenus < 2) {
            return false;
        }

        foreach ($menus as $key => $menu) {
            if (($menu->id == $id)) {
                $swapKey = null;

                if ($direction === 'up') {
                    $swapKey = ($key === 0) ? $countMenus - 1 : $key - 1;
                }

                if ($direction === 'down') {
                    $swapKey = ($key === ($countMenus - 1)) ? 0 : $key + 1;
                }

                $swapMenu = $menus[$swapKey] ?? null;

                if (! $swapMenu) {
                    continue;
                }

                $positionKey = $menu->position;

                $menu->position = $swapMenu->position;
                $menu->save();

                $swapMenu->position = $positionKey;
                $swapMenu->save();
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, Page>  $pagesByMenu
     * @return array<int, Page>
     */
    public function pagesPublishedTree(Collection $pagesByMenu)
    {
        $tree = [];

        foreach ($pagesByMenu as $page) {
            if ($page->page_id === null) {
                $tree[$page->id] = $page;
            }
        }

        foreach ($pagesByMenu as $page) {
            if ($page->page_id === null) {
                continue;
            }

            $parentId = $page->page_id;

            if (! isset($tree[$parentId])) {
                continue;
            }

            $parent = $tree[$parentId];

            $children = $parent->children ?? [];
            $children[] = $page;

            $parent->setAttribute('children', $children);
        }

        return $tree;
    }
}
