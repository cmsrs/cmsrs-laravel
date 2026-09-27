<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Tag;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Tag\TagCategory;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Tag\TagCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TagCategoryController extends Controller
{
    /**
     * @var array<string, string>
     */
    private array $validationRules = [];

    public function __construct(
        protected ConfigService $configService,
        protected TagCategoryService $tagCategoryService,
    ) {
        $langs = $this->configService->arrGetLangs();

        foreach ($langs as $lang) {
            $this->validationRules['name.'.$lang] =
                'max:255|required';
        }
    }

    public function index(): JsonResponse
    {
        $categories =
            $this->tagCategoryService->getAllTagCategories();

        $data = [];

        foreach ($categories as $category) {
            $data[] =
                $this->tagCategoryService->toApi($category);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->only('name');

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

        $valid =
            $this->tagCategoryService
                ->checkIsDuplicateName($data);

        if (empty($valid['success'])) {
            return response()->json($valid, 200);
        }

        try {
            $this->tagCategoryService
                ->createTagCategory($data);
        } catch (\Exception $e) {
            Log::error(
                'tag category add ex: '.$e->getMessage()
            );

            return response()->json([
                'success' => false,
                'error' => 'Add tag category problem - exception',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function show(TagCategory $tagCategory): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->tagCategoryService
                ->toApi($tagCategory),
        ], 200);
    }

    public function update(
        Request $request,
        TagCategory $tagCategory
    ): JsonResponse {
        $data = $request->only('name');

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

        $valid =
            $this->tagCategoryService
                ->checkIsDuplicateName(
                    $data,
                    $tagCategory->id
                );

        if (empty($valid['success'])) {
            return response()->json($valid, 200);
        }

        try {
            $res =
                $this->tagCategoryService
                    ->updateTagCategory(
                        $tagCategory,
                        $data
                    );
        } catch (\Exception $e) {
            Log::error(
                'tag category update ex: '.
                $e->getMessage().
                ' for: '.
                var_export($e, true)
            );

            return response()->json([
                'success' => false,
                'error' => 'Update tag category problem - exception',
            ], 200);
        }

        if (empty($res)) {
            return response()->json([
                'success' => false,
                'error' => 'Update tag category problem',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function delete(TagCategory $tagCategory): JsonResponse
    {
        $res =
            $this->tagCategoryService
                ->deleteTagCategory($tagCategory);

        if (empty($res)) {
            return response()->json([
                'success' => false,
                'error' => 'Delete tag category problem',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }
}
