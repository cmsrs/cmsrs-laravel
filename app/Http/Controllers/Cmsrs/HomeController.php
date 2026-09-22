<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs;

use App\Http\Controllers\Controller;
use App\Models\Cmsrs\Cms\Menu;
use App\Services\Cmsrs\CheckoutService;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\ArrObjHelperService;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\OrderService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\Product\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * @var Collection<int, Menu>
     */
    private $menus;

    /**
     * @var array<int, string>
     */
    private $langs;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ConfigService $configService,
        protected MenuService $menuService,
        protected PageService $pageService,
        protected ProductService $productService,
        protected OrderService $orderService,
        protected CheckoutService $checkoutService,

    ) {
        // $lang = Config::getLangFromSession();  //not working proper
        // App::setLocale($lang);

        $this->menus = $this->menuService->getMenu(); // $menus;
        $this->langs = $this->configService->arrGetLangs();
    }

    /**
     * Show the application dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $lang = App::getLocale();

        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('login');
        }

        $arrOrders = $this->orderService->inOrdersByUserId($user->id)->toArray();
        $orders = [];
        if (! empty($arrOrders)) {
            $arrOrdersReindex = ArrObjHelperService::reIndexArr($arrOrders, 'product_id');
            $dataToPayment = $this->productService->createPaymentData($arrOrdersReindex);
            $orders = $dataToPayment['orders'];
        }

        $objCheckouts = $this->checkoutService->findActiveOrdersForUser($user->id);
        $checkouts = $this->checkoutService->printCheckouts($objCheckouts, $lang);

        $data = [
            'checkouts' => $checkouts,
            'orders' => $orders,
            'lang' => $lang,
            'langs' => $this->langs,
            'menus' => $this->menus,
        ];

        return view('cmsrs.home', $data);
    }
}
