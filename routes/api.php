<?php

use App\Http\Controllers\Cmsrs\Api\AuthController;
use App\Http\Controllers\Cmsrs\Api\Cms\CommentController;
use App\Http\Controllers\Cmsrs\Api\Cms\ContactController;
use App\Http\Controllers\Cmsrs\Api\Cms\MenuController;
use App\Http\Controllers\Cmsrs\Api\Cms\PageController;
use App\Http\Controllers\Cmsrs\Api\ConfigController;
use App\Http\Controllers\Cmsrs\Api\HeadlessController;
use App\Http\Controllers\Cmsrs\Api\ImageController;
use App\Http\Controllers\Cmsrs\Api\Shop\CheckoutController;
use App\Http\Controllers\Cmsrs\Api\Shop\ProductController;
use App\Http\Controllers\Cmsrs\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your Application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

if (config('cmsrs.features.shop')) {
    Route::get('productsGetNameAndPrice/{lang?}', [ProductController::class, 'getNameAndPrice']);
}

Route::post('login', [AuthController::class, 'login']);
Route::get('comments/{page}', [CommentController::class,  'index'])->whereNumber('page');
Route::post('comments/{page}', [CommentController::class, 'create'])->whereNumber('page');
Route::post('contact/{lang}', [ContactController::class, 'create']);

/* is_headless start */
if (config('cmsrs.is_headless')) {
    Route::get('headless/pages-short-title/{shortTitle}', [HeadlessController::class, 'getPagesByShortTitle']);
    Route::get('headless/pages-type/{type}', [HeadlessController::class, 'getAllPagesByType']);
    Route::get('headless/page/{page}/{lang}', [HeadlessController::class, 'onePageItemByLang'])->whereNumber('page');
    Route::get('headless/menus', [HeadlessController::class, 'getMenus']);
    Route::get('headless/config', [HeadlessController::class, 'config']);
}
/* is_headless stop */

Route::group(['middleware' => ['jwt.auth']], function () {
    $apiSecret = config('cmsrs.api_secret');
    if ($apiSecret) {
        $apiSecret = $apiSecret.'/';
    }

    Route::get($apiSecret.'logout', [AuthController::class, 'logout']);

    Route::get($apiSecret.'pages', [PageController::class, 'index']);
    Route::get($apiSecret.'pages/{page}', [PageController::class, 'oneItemAdmin'])->whereNumber('page'); // ->where('id', '[0-9]+');
    Route::post($apiSecret.'pages', [PageController::class, 'create']);
    Route::put($apiSecret.'pages/{page}', [PageController::class, 'update'])->whereNumber('page');
    Route::delete($apiSecret.'pages/{page}', [PageController::class, 'delete'])->whereNumber('page');
    Route::patch($apiSecret.'pages/position/{direction}/{page}', [PageController::class, 'position'])->whereNumber('page');
    Route::get($apiSecret.'pages/type/{type}', [PageController::class, 'getPagesByType']);

    Route::get($apiSecret.'images/{type}/{pageId}', [ImageController::class, 'getItemByTypeAndRefId']); // getItemByPageId, it can be pageId or productId
    Route::post($apiSecret.'image/{type}/{pageId}', [ImageController::class, 'uploadImageByTypeAndRefId']); // it can be pageId or productId
    Route::delete($apiSecret.'images/{id}', [ImageController::class, 'delete']); // delete one or more images, id can be 1 or 1,2,3
    Route::patch($apiSecret.'images/position/{direction}/{image}', [ImageController::class, 'position'])->whereNumber('image'); // only for type page and product is working

    Route::get($apiSecret.'users/clients', [UserController::class, 'getClients']);
    Route::get($apiSecret.'clients/{column}/{direction}', [UserController::class, 'getClientsPaginateAndSort']);
    Route::post($apiSecret.'clients', [UserController::class, 'createClient']);
    Route::put($apiSecret.'clients/{user}', [UserController::class, 'updateClient'])->whereNumber('user');
    Route::delete($apiSecret.'clients/{user}', [UserController::class, 'deleteClient'])->whereNumber('user');
    Route::get($apiSecret.'clients/{user}', [UserController::class, 'getClient'])->whereNumber('user');

    Route::get($apiSecret.'menus', [MenuController::class, 'index']);
    Route::post($apiSecret.'menus', [MenuController::class, 'create']);
    Route::put($apiSecret.'menus/{menu}', [MenuController::class, 'update'])->whereNumber('menu');
    Route::delete($apiSecret.'menus/{menu}', [MenuController::class, 'delete'])->whereNumber('menu');
    Route::patch($apiSecret.'menus/position/{direction}/{menu}', [MenuController::class, 'position'])->whereNumber('menu');

    Route::get($apiSecret.'contacts', [ContactController::class, 'index']);
    Route::get($apiSecret.'contacts/pagination/{column}/{direction}', [ContactController::class, 'getItemsWithPaginateAndSort']);
    Route::delete($apiSecret.'contacts/{contact}', [ContactController::class, 'delete'])->whereNumber('contact');

    Route::get($apiSecret.'config', [ConfigController::class, 'index']);
    Route::put($apiSecret.'config/clearcache', [ConfigController::class, 'clearCache']);
    Route::put($apiSecret.'config/createsitemap', [ConfigController::class, 'createSiteMap']);
    Route::post($apiSecret.'config/toggle-cache-enable-file', [ConfigController::class, 'toggleCacheEnableFile']);
    Route::get($apiSecret.'config/is-cache-enable', [ConfigController::class, 'isCacheEnable']);

    /* shop start */
    if (config('cmsrs.features.shop')) {
        Route::get($apiSecret.'products', [ProductController::class, 'index']);
        Route::post($apiSecret.'products', [ProductController::class, 'create']);
        Route::get($apiSecret.'products/{product}', [ProductController::class, 'getItem'])->whereNumber('product');
        Route::get($apiSecret.'products/pagination/{lang}/{column}/{direction}', [ProductController::class, 'getItemsWithPaginateAndSort']);
        Route::put($apiSecret.'products/{product}', [ProductController::class, 'update'])->whereNumber('product');
        Route::delete($apiSecret.'products/{product}', [ProductController::class, 'delete'])->whereNumber('product');

        Route::get($apiSecret.'checkouts', [CheckoutController::class, 'index']);
        Route::get($apiSecret.'checkouts/pagination/{lang}/{column}/{direction}', [CheckoutController::class, 'getItemsWithPaginateAndSort']);
        Route::patch($apiSecret.'checkouts/{checkout}', [CheckoutController::class, 'update'])->whereNumber('checkout');
    }
    /* shop stop */
});
