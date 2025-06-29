<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ControlUserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\GenderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ListOfUserController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShiftController;
use App\Models\EmployeeModel;
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

// Auth
Route::post('login',[AuthController::class,'login']);
Route::middleware('auth:sanctum')->post('register',[AuthController::class,'registerStaff']);
Route::middleware('auth:sanctum')->post('account/staff/update/{id}',[AuthController::class,'update']);
Route::post('staff/refresh-token', [AuthController::class, 'refreshToken']);
Route::middleware('auth:sanctum')->post('logout',[AuthController::class,'logout']);

// Client Auth
Route::post('client/login',[ClientAuthController::class,'login']);
Route::post('client/register',[ClientAuthController::class,'registerClient']);
Route::middleware('auth:sanctum')->post('account/client/update/{id}',[ClientAuthController::class,'update']);
Route::post('client/refresh-token', [ClientAuthController::class, 'refreshToken']);
Route::middleware('auth:sanctum')->post('client/logout',[ClientAuthController::class,'logout']);

// Routes accessible only by user clients
Route::middleware(['auth:client', 'verified'])->group(function () {
    // List Product
    Route::get('client/product', [ProductController::class, 'list']);
    // List Category
    Route::get('client/category', [CategoryController::class, 'list']);
    // List sale client and client order action
    Route::get('saleClient/list', [ClientController::class, 'listForClient']); 
    Route::post('saleClient/add', [ClientController::class, 'add']);
    Route::post('saleClient/cancel', [ClientController::class, 'cancelOrder']);
    Route::post('saleClient/confirm/{id}', [ClientController::class, 'confirmReceived']);


});


// Routes accessible by staff/admin
Route::middleware(['auth:api'])->group(function () {
    // Category API
    Route::get('category', [CategoryController::class , 'list']);
    Route::get('category/{id}', [CategoryController::class , 'getById']);
    Route::post('category/add', [CategoryController::class , 'add']);
    Route::post('category/update/{id}', [CategoryController::class, 'update']);
    Route::delete('category/delete/{id}', [CategoryController::class, 'delete']);

    // Product API
    Route::get('product', [ProductController::class, 'list']);
    Route::get('product/{id}', [ProductController::class, 'getById']);
    Route::post('product/add', [ProductController::class, 'add']);
    Route::post('product/update/{id}', [ProductController::class, 'update']);
    Route::delete('product/delete/{id}', [ProductController::class, 'delete']);

    // Sale API
    Route::get('sale', [SaleController::class, 'list']);
    Route::get('sale/{id}', [SaleController::class, 'getById'])->where('id', '[0-9]+');
    Route::post('sale/add', [SaleController::class, 'add']);
    Route::post('sale/update/{id}', [SaleController::class, 'update']);
    Route::delete('sale/delete/{id}', [SaleController::class, 'delete']);
    Route::post('sale/update/status/{id}', [SaleController::class, 'updateStatus']);
    Route::post('sale/update/status', [SaleController::class, 'updateStatusBatch']);


    // Position
    Route::get('position',[PositionController::class ,'list']);
    Route::get('position/{id}',[PositionController::class ,'getById']);
    Route::post('position/add',[PositionController::class ,'add']);
    Route::post('position/update/{id}',[PositionController::class ,'update']);
    Route::delete('position/delete/{id}',[PositionController::class ,'delete']);

    // Role
    Route::get('role',[RoleController::class ,'list']);
    Route::get('role/{id}',[RoleController::class ,'getById']);
    Route::post('role/add',[RoleController::class ,'add']);
    Route::post('role/update/{id}',[RoleController::class ,'update']);
    Route::delete('role/delete/{id}',[RoleController::class ,'delete']);

    // Employee
    Route::get('employee',[EmployeeController::class,'list']);
    Route::get('employee/{id}',[EmployeeController::class,'getById']);
    Route::post('employee/add',[EmployeeController::class,'add']);
    Route::post('employee/update/{id}',[EmployeeController::class,'update']);
    Route::delete('employee/delete/{id}',[EmployeeController::class,'delete']);

    // Exchange
    Route::get('exchange',[ExchangeRateController::class,'list']);
    Route::get('exchange/{id}',[ExchangeRateController::class,'getById']);
    Route::post('exchange/add',[ExchangeRateController::class,'add']);
    Route::post('exchange/update/{id}',[ExchangeRateController::class,'update']);
    Route::delete('exchange/delete/{id}',[ExchangeRateController::class,'delete']);

    // Gender 
    Route::get('gender',[GenderController::class,'list']);
    // Province
    Route::get('province',[ProvinceController::class,'list']);

    // Shift 
    Route::get('shift',[ShiftController::class,'list']);
    Route::get('shift/{id}',[ShiftController::class,'getById']);
    Route::post('shift/start',[ShiftController::class,'startShift']);
    Route::post('shift/end/{id}',[ShiftController::class,'endShift']);

    // Control User Account
    Route::post('ban/user/{id}',[ControlUserController::class,'bannedUser']);
    Route::delete('delete/user/{id}',[ControlUserController::class,'deleteUser']);
    Route::post('ban/client/{id}',[ControlUserController::class,'bannedUserClient']);
    Route::delete('delete/client/{id}',[ControlUserController::class,'deleteUserClient']);

    // List of user
    Route::get('user/list',[ListOfUserController::class,'listStaff']);
    Route::get('user/list/{id}',[ListOfUserController::class,'getUserStaffById']);
    Route::get('userClient/list',[ListOfUserController::class,'listClient']);
    Route::get('userClient/list/{id}',[ListOfUserController::class,'getUserClientById']);
   
    // Invoice
    Route::get('invoice',[InvoiceController::class,'list']);
    Route::get('invoice/{id}',[InvoiceController::class,'getById']);

});
