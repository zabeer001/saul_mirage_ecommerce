<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\NewsLetterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingnReviewsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SubscribeController;
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



// Password reset routes
Route::post('forget-password', [NewPasswordController::class, 'forgetPassword']);
Route::post('forget/password/reset', [NewPasswordController::class, 'reset'])->name('password.reset');

Route::post('password/email', [AuthController::class, 'sendResetOTP']);
Route::post('password/verify-otp', [AuthController::class, 'verifyResetOTP'])->name('password.verify-otp');
Route::post('password/reset', [AuthController::class, 'passwordReset'])->name('password.reset');
Route::post('password/reset-for-auth-user', [AuthController::class, 'passwordResetForAuthUser']);
Route::post('change-profile-details', [AuthController::class, 'changeProfileDetails']);

Route::post('google/auth/jwt-process', [GoogleController::class, 'process']);




Route::post('image-update', [UserImageController::class, 'Update'])->middleware('auth:api');

//categories
Route::apiResource('categories', CategoryController::class);
Route::get('categories-by-type', [CategoryController::class, 'categoriesByType']);

//products
Route::apiResource('products', ProductController::class);
Route::get('best-selling-products', [ProductController::class, 'bestSellingProducts']);
Route::get('products-stats', [ProductController::class, 'stats']);



Route::apiResource('promocodes', PromoCodeController::class);
Route::get('promocode-stats', [PromoCodeController::class, 'stats']);

Route::apiResource('orders', OrderController::class);
Route::get('order-user', [OrderController::class, 'history']);
Route::get('orders/{uniq_id}', [OrderController::class, 'show']);
Route::get('order-stats', [OrderController::class, 'last_six_months_stats']);
Route::get('order-stats-three', [OrderController::class, 'orderStatsThree']);
Route::get('order-stats-table', [OrderController::class, 'stats']);
Route::get('self-order-history', [OrderController::class, 'selfOrderHistory']);
Route::post('orders-status/{id}', [OrderController::class, 'changeStatus']);


/** Stripe Routes */
Route::post('stripe/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout');
Route::get('stripe/success', [StripeController::class, 'checkoutSuccess'])->name('stripe.success');
Route::get('stripe/cancel', [StripeController::class, 'checkoutCancel'])->name('stripe.cancel');

Route::apiResource('newsletter', NewsLetterController::class);

Route::apiResource('reviews', ReviewController::class);
Route::get('reviews-home-page', [ReviewController::class, 'reviewsHomePage']);


Route::apiResource('customers', CustomerController::class);
Route::get('customers-stats', [CustomerController::class, 'stats']);

Route::post('send-contact-mail', [ContactController::class, 'sendContactMessage']);
Route::post('subscribe', [SubscribeController::class, 'sendSubscribeMail']);


Route::get('google/auth/redirect', [GoogleController::class, 'redirectToGoogle']);
Route::get('google/auth/callback', [GoogleController::class, 'handleGoogleCallback']);
