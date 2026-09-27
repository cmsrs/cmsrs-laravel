<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Tag;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Tag\Tag;
use App\Models\Cmsrs\Tag\TagCategory;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Tag\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * @var array<string, string>
     */
    private array $validationRules = [
        'tag_category_id' => 'required|integer',
    ];

    public function __construct(
        protected ConfigService $configService,
        protected TagService $tagService,
    ) {
        $langs = $this->configService->arrGetLangs();

        foreach ($langs as $lang) {
            $this->validationRules['name.'.$lang] =
                'max:255|required';
        }
    }

    public function index(): JsonResponse
    {
        $tags = $this->tagService->getAllTags();

        $data = [];

        foreach ($tags as $tag) {
            $data[] = $this->tagService->toApi($tag);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->only([
            'tag_category_id',
            'name',
        ]);

        $validator = Validator::make(
            $data,
            $this->validationRules
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->messages(),
            ], 200);
        }

        if (
            ! TagCategory::query()
                ->where('id', $data['tag_category_id'])
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'error' => 'Tag category does not exist',
            ], 200);
        }

        $valid = $this->tagService
            ->checkIsDuplicateName($data);

        if (empty($valid['success'])) {
            return response()->json($valid, 200);
        }

        try {
            $this->tagService->createTag($data);
        } catch (\Exception $e) {
            Log::error(
                'tag add ex: '.$e->getMessage()
            );

            return response()->json([
                'success' => false,
                'error' => 'Add tag problem - exception',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function show(Tag $tag): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->tagService->toApi($tag),
        ], 200);
    }

    public function update(
        Request $request,
        Tag $tag
    ): JsonResponse {
        $data = $request->only([
            'tag_category_id',
            'name',
        ]);

        $validator = Validator::make(
            $data,
            $this->validationRules
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->messages(),
            ], 200);
        }

        if (
            ! TagCategory::query()
                ->where('id', $data['tag_category_id'])
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'error' => 'Tag category does not exist',
            ], 200);
        }

        $valid = $this->tagService
            ->checkIsDuplicateName(
                $data,
                $tag->id
            );

        if (empty($valid['success'])) {
            return response()->json($valid, 200);
        }

        try {
            $res = $this->tagService
                ->updateTag($tag, $data);
        } catch (\Exception $e) {
            Log::error(
                'tag update ex: '.
                $e->getMessage().
                ' for: '.
                var_export($e, true)
            );

            return response()->json([
                'success' => false,
                'error' => 'Update tag problem - exception',
            ], 200);
        }

        if (empty($res)) {
            return response()->json([
                'success' => false,
                'error' => 'Update tag problem',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function delete(Tag $tag): JsonResponse
    {
        $res = $this->tagService->deleteTag($tag);

        if (empty($res)) {
            return response()->json([
                'success' => false,
                'error' => 'Delete tag problem',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }
}
