<?php

declare(strict_types=1);

namespace App\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Page;
use App\Services\Cmsrs\Helpers\LangHelperService;
use App\Services\Cmsrs\Page\PageService;

class HeadlessService
{
    public function __construct(private PageService $pageService, private ImageService $imageService) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPagesByShortTitleWithImages(string $shortTitle): array
    {
        $defaultLang = ConfigService::getDefaultLang();

        $pages = Page::with(['translates', 'contents'])
            ->where('published', true)
            ->where('after_login', false)
            ->whereHas('translates', function ($query) use ($shortTitle, $defaultLang) {
                $query->where('lang', $defaultLang)
                    ->where('column', 'short_title')
                    ->where('value', 'like', "%$shortTitle%");
            })
            ->orderBy('position', 'asc')
            ->get(Page::FIELDS)
            ->toArray();

        $i = 0;
        $out = [];
        foreach ($pages as $page) {
            $out[$i] = $this->pageService->getPageDataFormat($page);
            $out[$i]['images'] = $this->imageService->getImagesAndThumbsByTypeAndRefId('page', $page['id']);
            $i++;
        }

        return $out;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllPagesWithImages(string $type): array
    {
        if (! in_array($type, ConfigService::arrGetPageTypes())) {
            throw new \Exception('Wrong type : '.$type);
        }

        $pages = Page::with(['translates', 'contents'])->where('type', $type)->where('published', true)->where('after_login', false)->orderBy('position', 'asc')->get(Page::FIELDS)->toArray();

        $i = 0;
        $out = [];
        foreach ($pages as $page) {
            $out[$i] = $this->pageService->getPageDataFormat($page);
            $out[$i]['images'] = $this->imageService->getImagesAndThumbsByTypeAndRefId('page', $page['id']);
            $i++;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $page
     * @return array<string, mixed>
     */
    public function getPageDataFormatByLang(array $page, string $lang): array
    {
        $data = $this->pageService->getPageDataFormat($page);

        return LangHelperService::removeKeyLangInArr($data, $lang);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllPagesWithImagesOneItemByLang(Page $mPage, string $lang): array
    {
        $pageModel = Page::with(['translates', 'contents'])->find($mPage->id);
        $page = $pageModel?->toArray() ?? [];

        $formatPage = $this->getPageDataFormatByLang($page, $lang);
        $formatPage['images'] = $this->imageService->getImagesAndThumbsByTypeAndRefId('page', $mPage->id, $lang);

        return $formatPage;
    }
}
