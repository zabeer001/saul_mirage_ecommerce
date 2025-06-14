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


Route::apiResource('categories', CategoryController::class);
Route::get('categories-by-type', [CategoryController::class, 'categoriesByType']);
Route::apiResource('products', ProductController::class);
Route::get('best-selling-products', [ProductController::class, 'bestSellingProducts']);

Route::apiResource('promocodes', PromoCodeController::class);

Route::apiResource('orders', OrderController::class);
Route::get('/orders/{uniq_id}', [OrderController::class, 'show']);
Route::get('order-stats', [OrderController::class, 'last_six_months_stats']);

Route::apiResource('newsletter', NewsLetterController::class);

Route::apiResource('reviews', ReviewController::class);

Route::apiResource('customers', CustomerController::class);








