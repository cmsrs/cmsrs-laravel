<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Tag;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Tag\Tag;
use Illuminate\Http\JsonResponse;

class tagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::all();

        return response()->json(['success' => true, 'data' => $tags], 200);
    }
}
