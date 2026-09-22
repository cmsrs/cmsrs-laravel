<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Cms\Menu;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function __construct(
        protected ConfigService $configService,
        protected MenuService $menuService,
    ) {
        $langs = $this->configService->arrGetLangs();
        foreach ($langs as $lang) {
            $this->validationRules['name.'.$lang] = 'max:255|required'; // |unique:App\Translate,value,null,menu_id';
        }
    }

    /**
     * @var array<string, string>
     */
    private $validationRules = [
        'position' => 'numeric',
    ];

    public function index(): JsonResponse
    {
        $menus = $this->menuService->getAllMenus();

        return response()->json(['success' => true, 'data' => $menus], 200);
    }

    public function position(Request $request, string $direction, Menu $menu): JsonResponse
    {
        $ret = $this->menuService->swapPosition($direction, $menu->id);

        return response()->json(['success' => $ret]);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->only('name');

        $validator = Validator::make($data, $this->validationRules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()], 200);
        }

        // check unique
        $valid = $this->menuService->checkIsDuplicateName($data);
        if (empty($valid['success'])) {
            return response()->json($valid, 200);
        }

        try {
            $this->menuService->wrapCreate($data);
        } catch (\Exception $e) {
            Log::error('menu add ex: '.$e->getMessage());

            return response()->json(['success' => false, 'error' => 'Add menu problem - exception'], 200);
        }

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Menu $menu): JsonResponse
    {
        $data = $request->only('name');

        $validator = Validator::make($data, $this->validationRules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()], 200);
        }

        // check unique
        $valid = $this->menuService->checkIsDuplicateName($data, $menu->id);
        if (empty($valid['success'])) {
            return response()->json($valid, 200);
        }

        try {
            $res = $this->menuService->wrapUpdate($menu, $data);
        } catch (\Exception $e) {
            Log::error('menu update ex: '.$e->getMessage().' for: '.var_export($e, true));

            return response()->json(['success' => false, 'error' => 'Update menu problem - exeption'], 200);
        }

        if (empty($res)) {
            return response()->json(['success' => false, 'error' => 'Update menu problem'], 200);
        }

        return response()->json(['success' => true], 200);
    }

    public function delete(Request $request, Menu $menu): JsonResponse
    {
        $res = $menu->delete();
        if (empty($res)) {
            return response()->json(['success' => false, 'error' => 'Update delete problem'], 200);
        }

        return response()->json(['success' => true], 200);
    }
}
