<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Shop\Product;
use App\Models\Cmsrs\Tag\Tag;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Tag\TaggableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductTagController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $langs = [];

    public function __construct(
        protected ConfigService $configService,
        protected TaggableService $taggableService,
    ) {
        $this->langs = $this->configService->arrGetLangs();
    }

    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->taggableService
                ->getTags($product),
        ], 200);
    }

    public function update(
        Request $request,
        Product $product
    ): JsonResponse {
        $rules = [
            'tags' => [
                'present',
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

        $tags = $validator->validated()['tags'] ?? [];

        try {
            $this->taggableService->updateTags(
                $product,
                $tags
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Update product tags problem',
            ], 200);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function productsByTag(Tag $tag): JsonResponse
    {
        $products = $this->taggableService
            ->getproductsByTag($tag);

        $data = [];

        foreach ($products as $product) {
            /** @var Product&object{pivot: object{lang: string}} $product */
            $data[] = [
                'id' => $product->id,
                'lang' => $product->pivot->lang,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
