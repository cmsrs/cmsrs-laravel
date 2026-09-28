<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use App\Services\Cmsrs\Tag\PageTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PageTagController extends Controller
{
    public function __construct(
        protected PageTagService $pageTagService,
    ) {}

    public function index(Page $page): JsonResponse
    {
        $tags = $this->pageTagService->getPageTags($page);

        return response()->json([
            'success' => true,
            'data' => $tags,
        ], 200);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'tags' => ['required', 'array'],
                'tags.*' => ['array'],
                'tags.*.*' => ['integer', 'distinct'],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->messages(),
            ], 200);
        }

        try {
            /** @var array<string, array<int, int>> $tags */
            $tags = $request->input('tags');

            $this->pageTagService->syncPageTags($page, $tags);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function pagesByTag(Request $request, Tag $tag): JsonResponse
    {
        $lang = $request->query('lang');

        if ($lang !== null && ! is_string($lang)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid language',
            ], 200);
        }

        $pages = $this->pageTagService->getPagesByTag($tag, $lang);

        return response()->json([
            'success' => true,
            'data' => $pages,
        ], 200);
    }
}
