<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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

Route::post('login', [Controllers\API\AuthController::class, 'login']);
Route::post('register', [Controllers\API\AuthController::class, 'register']);
Route::post('forgot_password', [Controllers\API\AuthController::class, 'forgot_password']);
Route::post('validate_otp', [Controllers\API\AuthController::class, 'validate_otp']);
Route::post('reset_password', [Controllers\API\AuthController::class, 'reset_password']);


Route::prefix('user')->group(function(){
    Route::get('customers', [Controllers\API\UserController::class, 'customers'])->middleware('auth');
    Route::get('vendors', [Controllers\API\UserController::class, 'vendors']);
    Route::middleware(['auth', 'admin'])->group(function(){
        Route::get('admins', [Controllers\API\UserController::class, 'admins']);
        Route::get('stats', [Controllers\API\UserController::class, 'statistics']);
    });
    Route::get('profile/{id}', [Controllers\API\UserController::class, 'show'])->middleware('auth');
    Route::post('profile/{id}', [Controllers\API\UserController::class, 'update'])->middleware('auth');
    Route::post('change_password', [Controllers\API\UserController::class, 'change_password'])->middleware('auth');
    Route::get('search', [Controllers\API\UserController::class, 'search']);
});

Route::prefix('businesses')->group(function(){
    Route::get('index', [Controllers\API\BusinessController::class, 'index']);
    Route::get('show/{id}', [Controllers\API\BusinessController::class, 'show']);
    Route::get('search', [Controllers\API\BusinessController::class, 'search']);
    Route::middleware('auth')->group(function(){
        Route::post('create', [Controllers\API\BusinessController::class, 'create']);
        Route::post('update/{id}', [Controllers\API\BusinessController::class, 'update']);
        Route::post('update_logo/{id}', [Controllers\API\ImageController::class, 'upload_business_logo']);
        Route::post('delete/{id}', [Controllers\API\BusinessController::class, 'delete']);
    });
});

Route::prefix('products')->group(function(){
    Route::get('index', [Controllers\API\ProductController::class, 'index']);
    Route::get('show/{id}', [Controllers\API\ProductController::class, 'show']);
    Route::get('images/{id}', [Controllers\API\ImageController::class, 'get_product_images']);
    Route::get('search', [Controllers\API\ProductController::class, 'search']);
    Route::middleware('auth')->group(function(){
        Route::post('create', [Controllers\API\ProductController::class, 'create']);
        Route::post('update', [Controllers\API\ProductController::class, 'update']);
        Route::get('delete', [Controllers\API\ProductController::class, 'delete']);
        Route::post('images/upload/{id}', [Controllers\API\ImageController::class, 'upload_product_images']);
        Route::post('images/delete/{id}', [Controllers\API\ImageController::class, 'delete_product_images']);
    });
});

Route::prefix('categories')->group(function(){
    Route::get('/', [Controllers\API\HomeController::class, 'categories']);
    Route::middleware(['auth', 'admin'])->group(function(){
        Route::post('create', [Controllers\API\HomeController::class, 'create_category']);
        Route::post('update/{id}', [Controllers\API\HomeController::class, 'update_category']);
        Route::get('delete/{id}', [Controllers\API\HomeController::class, 'delete_category']);
    });
    Route::get('statistics', [Controllers\API\HomeController::class, 'summary_statistics']);
    Route::get('search', [Controllers\API\HomeController::class, 'search']);
});