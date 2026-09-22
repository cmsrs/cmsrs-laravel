<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Cms\Comment;
use App\Models\Cmsrs\Cms\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Validation rules for comment create
     *
     * @var array<string, string>
     */
    private $validationRules = [
        'content' => 'max:1280|required',
    ];

    public function create(Request $request, Page $page): JsonResponse
    {
        $data = $request->only(
            'content'
        );

        if (empty($page->commented)) {
            abort(404);
        }

        $data['page_id'] = $page->id;

        $validator = Validator::make($data, $this->validationRules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()], 200);
        }

        try {
            $comment = Comment::create($data);
            if (empty($comment->id)) {
                throw new \Exception('I cant get comment id');
            }
        } catch (\Exception $e) {
            Log::error('comment add ex: '.$e->getMessage().' line: '.$e->getLine().'  file: '.$e->getFile()); // .' for: '.var_export($data, true )

            return response()->json(['success' => false, 'error' => 'Add comment problem, details in the log file.'], 200); // .$e->getMessage()
        }

        return response()->json(['success' => true]);
    }

    public function index(Request $request, Page $page): JsonResponse
    {
        if (empty($page->commented)) {
            abort(404);
        }

        $comments = Comment::where('page_id', $page->id)->orderby('created_at', 'desc')->get(['content'])->toArray();

        return response()->json(['success' => true, 'data' => $comments], 200);
    }
}
