<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Category Api
Route::get('category',[CategoryController::class , 'list']);
Route::post('category/add',[CategoryController::class , 'add']);
Route::post('category/update/{id}', [CategoryController::class, 'update']);
Route::delete('category/delete/{id}', [CategoryController::class, 'delete']);

// Product Api
Route::get('product',[ProductController::class,'list']);
Route::post('product/add',[ProductController::class,'add']);
Route::post('product/update/{id}',[ProductController::class,'update']);
Route::delete('product/delete/{id}',[ProductController::class,'delete']);


// Exchange Rate
Route::get('exchange-rate',[ExchangeRateController::class ,'list']);
Route::post('exchange-rate/add',[ExchangeRateController::class ,'add']);
Route::post('exchange-rate/update/{id}',[ExchangeRateController::class ,'update']);
Route::delete('exchange-rate/delete/{id}',[ExchangeRateController::class ,'delete']);


// Sale
Route::get('sale',[SaleController::class,'list']);
Route::post('sale/add',[SaleController::class,'add']);