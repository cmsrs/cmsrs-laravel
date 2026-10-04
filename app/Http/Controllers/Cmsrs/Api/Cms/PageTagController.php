<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use App\Services\Cmsrs\Tag\TaggableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageTagController extends Controller
{
    public function __construct(
        protected TaggableService $taggableService,
    ) {}

    public function index(Page $page): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->taggableService
                ->getTags($page),
        ], 200);
    }

    public function update(
        Request $request,
        Page $page
    ): JsonResponse {
        $rules = [
            'tags' => [
                'present',
                'array',
            ],
        ];

        $rules['tags.*'] = [
            'integer',
            'exists:tags,id',
        ];

        $validator = Validator::make(
            $request->all(),
            $rules
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->messages(),
            ], 200);
        }

        $tags = $validator->validated()['tags'] ?? [];

        try {
            $this->taggableService->updateTags(
                $page,
                $tags
            );
        } catch (\Exception $e) {

            // printf(
            //     "Error updating page tags: %s\n%s\n",
            //     $e->getMessage(),
            //     $e->getTraceAsString()
            // );

            return response()->json([
                'success' => false,
                'error' => 'Update page tags problem',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function pagesByTag(Tag $tag): JsonResponse
    {
        $pages = $this->taggableService
            ->getPagesByTag($tag);

        $data = [];

        foreach ($pages as $page) {
            $data[] = [
                'id' => $page->id,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
