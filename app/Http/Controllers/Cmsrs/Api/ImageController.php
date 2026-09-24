<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Image;
use App\Services\Cmsrs\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    // private $validationRules = [
    //     'name' => 'max:255',
    // ];

    public function __construct(
        protected ImageService $imageService,
    ) {}

    public function getItemByTypeAndRefId(Request $request, string $type, string $refId): JsonResponse
    {

        if (empty(Image::$type[$type])) {
            return response()->json(['success' => false, 'error' => 'page type not exist'], 404);
        }

        if (! is_numeric($refId)) {
            return response()->json(['success' => false, 'error' => 'refId must be numeric'], 400);
        }

        $images = $this->imageService->getImagesAndThumbsByTypeAndRefId($type, (int) $refId);

        return response()->json(['success' => true, 'data' => $images], 200);
    }

    public function uploadImageByTypeAndRefId(Request $request, string $type, string $refId): JsonResponse
    {
        if (empty(Image::$type[$type])) {
            return response()->json(['success' => false, 'error' => 'page type not exist'], 404);
        }

        if (! is_numeric($refId)) {
            return response()->json(['success' => false, 'error' => 'refId must be numeric'], 400);
        }

        $modelClass = Image::$modelClassesByType[$type] ?? null;
        if ($modelClass === null) {
            return response()->json(['success' => false, 'error' => 'model class not found for type: '.$type], 404);
        }

        $obj = $modelClass::find($refId);
        if (empty($obj)) {
            return response()->json(['success' => false, 'error' => 'obj not found'], 404);
        }

        $dataImage = [
            'name' => (string) $request->input('name'),
            'data' => (string) $request->input('data'),
        ];
        try {
            $this->imageService->createImages([$dataImage], $type, $obj->id); //  (int)$refId);
        } catch (\Exception $e) {
            // 'Allowed memory size of 134217728 bytes exhausted - therefor i removed: .var_export($e, true) on server
            Log::error('upload images: '.$e->getMessage().' line: '.$e->getLine().'  file: '.$e->getFile());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 200);
        }

        return response()->json(['success' => true], 200);
    }

    public function position(Request $request, string $direction, Image $image): JsonResponse
    {
        $ret = $this->imageService->swapPosition($direction, $image->id);

        return response()->json(['success' => $ret]);
    }

    public function delete(Request $request, string $id): JsonResponse
    {
        $ids = explode(',', $id);

        foreach ($ids as $itemId) {
            $image = Image::find($itemId);

            if (empty($image)) {
                return response()->json(['success' => false, 'error' => 'Image not find id='.$itemId], 200);
            }

            $res = $this->imageService->delete($image);
            if (empty($res)) {
                return response()->json(['success' => false, 'error' => 'Image delete problem id='.$itemId], 200);
            }
        }

        return response()->json(['success' => true], 200);
    }
}
