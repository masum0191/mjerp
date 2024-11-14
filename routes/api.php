<?php

use App\Http\Controllers\DemoAutoUpdateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\BillerController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerController;

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

Route::controller(DemoAutoUpdateController::class)->group(function () {
    Route::get('fetch-data-general', 'fetchDataGeneral')->name('fetch-data-general');
    Route::get('fetch-data-upgrade', 'fetchDataForAutoUpgrade')->name('data-read');
    Route::get('fetch-data-bugs', 'fetchDataForBugs')->name('fetch-data-bugs');
});

Route::post('register',[UserAuthController::class,'register']);
Route::post('login',[UserAuthController::class,'login']);
Route::post('logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('sale-store', [SaleController::class, 'sale_store_api'])->middleware('auth:sanctum');
Route::post('add-payment', [SaleController::class, 'addPaymentApi'])->middleware('auth:sanctum');
Route::get('status-details', [Controller::class, 'status'])->middleware('auth:sanctum');
Route::get('sales-unit', [Controller::class, 'sales_unit'])->middleware('auth:sanctum');
Route::get('biller', [Controller::class, 'biller'])->middleware('auth:sanctum');
Route::get('wherehose', [Controller::class, 'wherehose'])->middleware('auth:sanctum');
Route::get('customer', [Controller::class, 'customer'])->middleware('auth:sanctum');
Route::post('products', [Controller::class, 'getProducts'])->middleware('auth:sanctum');
Route::get('sale-list', [Controller::class, 'sale_list'])->middleware('auth:sanctum');
Route::get('curency', [Controller::class, 'curency'])->middleware('auth:sanctum');
Route::post('customer-store', [CustomerController::class, 'custom_store_api'])->middleware('auth:sanctum');
Route::get('customer-edit/{customer_id}', [CustomerController::class, 'edit_api'])->middleware('auth:sanctum');
Route::post('customer-update/{customer_id}', [CustomerController::class, 'customer_api_update'])->middleware('auth:sanctum');
Route::get('biller-sale-report', [Controller::class, 'biller_sale_report'])->middleware('auth:sanctum');

Route::get('account-list', [Controller::class, 'account_list'])->middleware('auth:sanctum');
Route::get('payment-list', [BillerController::class, 'billerPaymentListApi'])->middleware('auth:sanctum');
Route::get('receive-payment/{pid}', [BillerController::class, 'biller_receive_api'])->middleware('auth:sanctum');
Route::get('admin-setelment-list', [BillerController::class, 'admin_setelment_api'])->middleware('auth:sanctum');
// adminpayment_api
Route::post('admin-pay', [BillerController::class, 'adminpayment_api_pay'])->middleware('auth:sanctum');