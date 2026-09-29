<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Tag\Tag;
use App\Services\Cmsrs\Cms\PageTagService;
use App\Services\Cmsrs\ConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageTagController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $langs = [];

    public function __construct(
        protected ConfigService $configService,
        protected PageTagService $pageTagService,
    ) {
        $this->langs = $this->configService->arrGetLangs();

        // if (is_array($langs)) {
        //     $this->langs = array_values($langs);
        // }

        // if (empty($this->langs)) {
        //     $this->langs = ['en', 'pl'];
        // }
    }

    public function index(Page $page): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pageTagService
                ->getTags($page),
        ], 200);
    }

    public function update(
        Request $request,
        Page $page
    ): JsonResponse {
        $rules = [
            'tags' => [
                'required',
                'array',
            ],
        ];

        foreach ($this->langs as $lang) {
            $rules['tags.'.$lang] = [
                'nullable',
                'array',
            ];

            $rules['tags.'.$lang.'.*'] = [
                'integer',
                'exists:tags,id',
            ];
        }

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

        $data = $validator->validated();

        try {
            $this->pageTagService->updateTags(
                $page,
                $data['tags']
            );
        } catch (\Exception $e) {
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
        $pages = $this->pageTagService
            ->getPagesByTag($tag);

        $data = [];

        foreach ($pages as $page) {
            /** @var Page&object{pivot: object{lang: string}} $page */
            $data[] = [
                'id' => $page->id,
                'lang' => $page->pivot->lang,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
