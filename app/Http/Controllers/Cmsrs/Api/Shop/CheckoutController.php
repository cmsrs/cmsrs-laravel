<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api\Shop;

use App\Enums\Cmsrs\SortDirection;
use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Shop\Checkout;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Shop\CheckoutService;
use App\Services\Cmsrs\Shop\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    /**
     * Validation rules for checkout update
     *
     * @var array<string, string>
     */
    private $validationRules = [
        'is_pay' => 'boolean',
    ];

    public function __construct(
        protected CheckoutService $checkoutService,
        protected ConfigService $configService,
        protected OrderService $orderService,
    ) {}

    public function index(): JsonResponse
    {
        $lang = ConfigService::getDefaultLang();
        $objCheckouts = Checkout::All();
        $checkouts = $this->checkoutService->printCheckouts($objCheckouts, $lang);

        return response()->json(['success' => true, 'data' => $checkouts], 200);
    }

    public function getItemsWithPaginateAndSort(Request $request, string $lang, string $column, string $direction): JsonResponse
    {
        $search = $request->input('search', null);

        if (! in_array($lang, $this->configService->arrGetLangs())) {
            return response()->json([
                'success' => false,
                'error' => 'wrong lang in url',
            ], 404);
        }

        $columnsAllowedToSort = (new Checkout)->columnsAllowedToSort;
        if (! in_array($column, $columnsAllowedToSort)) {
            return response()->json([
                'success' => false,
                'error' => 'available columns to sort checkouts: '.implode(',', $columnsAllowedToSort),
            ], 404);
        }

        $direction = SortDirection::tryFrom($direction);

        if (! $direction) {
            return response()->json([
                'success' => false,
                'error' => 'available direction to sort: '.implode(',', SortDirection::values()),
            ], 404);
        }

        $checkouts = $this->checkoutService->getPaginationItems($lang, $column, $direction, $search);

        return response()->json(['success' => true, 'data' => $checkouts], 200);
    }

    public function update(Request $request, Checkout $checkout): JsonResponse
    {
        $data = $request->only('is_pay');

        $validator = Validator::make($data, $this->validationRules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()], 200);
        }
        $beforeIsPay = $checkout->is_pay;

        try {
            $res = $checkout->update($data);
        } catch (\Exception $e) {
            Log::error('checkout update ex: '.$e->getMessage());

            return response()->json(['success' => false, 'error' => 'Update checkout problem - exception'], 200);
        }

        if (empty($res)) {
            return response()->json(['success' => false, 'error' => 'Update checkout problem'], 200);
        }

        if (empty($beforeIsPay) && ! empty($data['is_pay'])) {
            $this->orderService->copyDataFromBasketToOrderForUser($checkout);
        }

        return response()->json(['success' => true], 200);
    }
}
