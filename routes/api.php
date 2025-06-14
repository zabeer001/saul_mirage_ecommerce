<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NewsLetterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingnReviewsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserImageController;
use App\Models\NewsLetter;
use App\Models\RatingnReviews;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/* amit project */



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('image-update', [UserImageController::class, 'Update'])->middleware('auth:api');


Route::apiResource('categories', CategoryController::class);
Route::get('categories-by-type', [CategoryController::class, 'categoriesByType']);
Route::apiResource('products', ProductController::class);
Route::get('best-selling-products', [ProductController::class, 'bestSellingProducts']);
Route::get('products-stats', [ProductController::class, 'stats']);



Route::apiResource('promocodes', PromoCodeController::class);

Route::apiResource('orders', OrderController::class);
Route::get('/orders/{uniq_id}', [OrderController::class, 'show']);
Route::get('order-stats', [OrderController::class, 'last_six_months_stats']);
Route::get('order-stats-three', [OrderController::class, 'orderStatsThree']);
Route::get('order-stats-table', [OrderController::class, 'stats']);
Route::get('self-order-history', [OrderController::class, 'selfOrderHistory']);


Route::apiResource('newsletter', NewsLetterController::class);

Route::apiResource('reviews', ReviewController::class);

Route::apiResource('customers', CustomerController::class);
Route::get('customers-stats', [CustomerController::class, 'stats']);








