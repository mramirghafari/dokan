<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CrmPublicApiController;
use App\Http\Controllers\Api\EcommerceController;
use App\Http\Controllers\Api\MobileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('mobile')->group(function () {
    Route::post('/login', [MobileController::class, 'login'])->name('api.mobile.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [MobileController::class, 'logout'])->name('api.mobile.logout');
        Route::get('/me', [MobileController::class, 'me'])->name('api.mobile.me');
        Route::get('/dashboard', [MobileController::class, 'dashboard'])->name('api.mobile.dashboard');
        Route::get('/customers', [MobileController::class, 'customers'])->name('api.mobile.customers');
        Route::get('/products', [MobileController::class, 'products'])->name('api.mobile.products');
        Route::get('/promotions', [MobileController::class, 'promotions'])->name('api.mobile.promotions');
        Route::get('/visitor/visit-plans', [MobileController::class, 'visitPlans'])->name('api.mobile.visitor.visitPlans');
        Route::post('/visitor/visit-stops/{visitStop}/check-in', [MobileController::class, 'checkInVisitStop'])->name('api.mobile.visitor.visitStops.checkIn');
        Route::post('/visitor/visit-stops/{visitStop}/no-order', [MobileController::class, 'noOrderVisitStop'])->name('api.mobile.visitor.visitStops.noOrder');
        Route::post('/orders', [MobileController::class, 'storeMobileOrder'])->name('api.mobile.orders.store');
        Route::get('/orders', [MobileController::class, 'orders'])->name('api.mobile.orders');
        Route::post('/sync/push', [MobileController::class, 'syncPush'])->name('api.mobile.sync.push');
        Route::get('/driver/shipments', [MobileController::class, 'driverShipments'])->name('api.mobile.driver.shipments');
        Route::post('/driver/stops/{shipmentRoute}/deliver', [MobileController::class, 'deliverStop'])->name('api.mobile.driver.stops.deliver');
        Route::post('/driver/stops/{shipmentRoute}/fail', [MobileController::class, 'failStop'])->name('api.mobile.driver.stops.fail');
    });
});

Route::prefix('ecommerce/{channelCode}')->group(function () {
    Route::get('/products', [EcommerceController::class, 'products'])->name('api.ecommerce.products');
    Route::post('/orders', [EcommerceController::class, 'storeOrder'])->name('api.ecommerce.orders.store');
    Route::post('/orders/{externalOrderId}/status', [EcommerceController::class, 'updateOrderStatus'])->name('api.ecommerce.orders.status');
});

Route::prefix('crm/{clientCode}')->group(function () {
    Route::get('/meta', [CrmPublicApiController::class, 'meta'])->name('api.crm.meta');
    Route::post('/leads', [CrmPublicApiController::class, 'storeLead'])->name('api.crm.leads.store');
    Route::post('/tickets', [CrmPublicApiController::class, 'storeTicket'])->name('api.crm.tickets.store');
    Route::post('/opportunities', [CrmPublicApiController::class, 'storeOpportunity'])->name('api.crm.opportunities.store');
});
