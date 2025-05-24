<?php

use App\Http\Controllers\ProjectRequestController;
use App\Http\Controllers\Body1Controller;
use App\Http\Controllers\Body2Controller;
use App\Http\Controllers\ReviewDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HeroController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AboutSec2Controller;
use App\Http\Controllers\AddressController; // Ensure this line exists and the class is defined in the specified namespace
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PossibleController;
use App\Http\Controllers\HomeController;
use App\Models\OurCoreValue;
use App\Http\Controllers\OurCoreValueController;
use App\Http\Controllers\ContactController;
use App\Models\WhyChooseUs;
use App\Http\Controllers\WhyChooseUsController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\FooterController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\NavbarController;
use App\Http\Controllers\OurContactController;
use App\Http\Controllers\PoweredByMrPcController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewContentController;

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


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Dynamic3 Background Design belongs from OurServices
Route::middleware('auth:api')->group(function () {
    Route::get('/services/background', [MenuController::class, 'show']);
    Route::post('/services/background', [MenuController::class, 'storeOrUpdate']);
});


// Dynamic3 heading from our service Design
Route::middleware('auth:api')->group(function () {
    Route::get('/services/heading', [HeroController::class, 'show']);
    Route::post('/services/heading', [HeroController::class, 'storeOrUpdate']);
});

// Dynamic3 project management from our service Design
Route::middleware('auth:api')->group(function () {
    Route::get('/services/projectmanagement', [Body1Controller::class, 'show']);
    Route::post('/services/projectmanagement', [Body1Controller::class, 'storeOrUpdate']);
});




// Dynamic3 support from our service Design
Route::middleware('auth:api')->group(function () {
    Route::get('/services/support', [Body2Controller::class, 'show']);
    Route::post('/services/support', [Body2Controller::class, 'storeOrUpdate']);
});


// Dynamic3 end to end delivery  from our service Design
Route::middleware('auth:api')->group(function () {
    Route::get('/services/delivery', [HomeController::class, 'show']);
    Route::post('/services/delivery', [HomeController::class, 'storeOrUpdate']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');




// Dynamic3 navbar Design
Route::middleware('auth:api')->group(function () {
    Route::post('/navbar', [NavbarController::class, 'storeOrUpdate']);
});

Route::get('/navbar', [NavbarController::class, 'show']);


// Dynamic3 About us Design
Route::middleware('auth:api')->group(function () {
    Route::get('/aboutus', [AboutController::class, 'show']);
    Route::post('/aboutus', [AboutController::class, 'storeOrUpdate']);
});

// Dynamic3 About us section2 Design
Route::middleware('auth:api')->group(function () {
    Route::get('/aboutus-section2', [AboutSec2Controller::class, 'show']);
    Route::post('/aboutus-section2', [AboutSec2Controller::class, 'storeOrUpdate']);
});

// Dynamic3 Banner Design
Route::middleware('auth:api')->group(function () {
    Route::get('/banner', [BannerController::class, 'show']);
    Route::post('/banner', [BannerController::class, 'storeOrUpdate']);
});





// Dynamic3 it from managed services Design
Route::middleware('auth:api')->group(function () {
    Route::get('/managedservices/it', [OurCoreValueController::class, 'show']);
    Route::post('/managedservices/it', [OurCoreValueController::class, 'storeOrUpdate']);
});



// Dynamic3 it from managed services Design
Route::middleware('auth:api')->group(function () {
    Route::get('/managedservices/whychooseus', [WhyChooseUsController::class, 'show']);
    Route::post('/managedservices/whychooseus', [WhyChooseUsController::class, 'storeOrUpdate']);
});



// Dynamic3 powered by MrPc from managed services Design
Route::middleware('auth:api')->group(function () {
    Route::get('/managedservices/poweredbymrpc', [PoweredByMrPcController::class, 'show']);
    Route::post('/managedservices/poweredbymrpc', [PoweredByMrPcController::class, 'storeOrUpdate']);
});


Route::middleware('auth:api')->group(function () {
    Route::get('/managedservices/projectmanagement', [ServiceController::class, 'show']);
    Route::post('/managedservices/projectmanagement', [ServiceController::class, 'storeOrUpdate']);
});


// Dynamic3 Contact Design
Route::middleware('auth:api')->group(function () {
    Route::get('/contact', [ContactController::class, 'show']);
    Route::post('/contact', [ContactController::class, 'storeOrUpdate']);
});


// Dynamic3 address Design
Route::middleware('auth:api')->group(function () {
    Route::get('/address', [AddressController::class, 'show']);
    Route::post('/address', [AddressController::class, 'storeOrUpdate']);
});

// Dynamic3 ourContact Design
Route::middleware('auth:api')->group(function () {
    Route::get('/ourcontact', [OurContactController::class, 'show']);
    Route::post('/ourcontact', [OurContactController::class, 'storeOrUpdate']);
});


// Dynamic3 review heading Design
Route::middleware('auth:api')->group(function () {
    Route::get('/review/heading', [ReviewController::class, 'show']);
    Route::post('/review/heading', [ReviewController::class, 'storeOrUpdate']);
});

// Dynamic3 review heading Design
Route::middleware('auth:api')->group(function () {
    Route::get('/review/content', [ReviewContentController::class, 'show']);
    Route::post('/review/content', [ReviewContentController::class, 'storeOrUpdate']);
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('review-data', ReviewDataController::class);
});
Route::get('/review-data-front', [ReviewDataController::class, 'getReviewData']);



Route::middleware('auth:api')->group(function () {
   
    Route::get('/contactMessage', [ContactMessageController::class, 'show']);
});




Route::get('/frontend-data', [FrontendController::class, 'getAllData']);
Route::post('/contactMessage', [ContactMessageController::class, 'store']);
// Route::post('/Message', [ContactMessageController::class, 'store']);


// Dynamic3 address Design
Route::middleware('auth:api')->group(function () {
    Route::get('/footer', [FooterController::class, 'show']);
    Route::post('/footer', [FooterController::class, 'storeOrUpdate']);
});

Route::apiResource('project-requests', ProjectRequestController::class);