<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SalonController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Discountcontroller;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingControllerr;

use function Pest\Laravel\put;

///////////////////////public Route
Route::post('customer', [CustomerController::class, 'createCustomer']);
Route::post('sendCode', [CustomerController::class, 'sendOtp']);
Route::post('login', [CustomerController::class, 'loginCustomer']);
Route::post('verifyCode', [CustomerController::class, 'verifyCode']);
Route::put('changePassword', [CustomerController::class, 'changePassword']);
Route::post('adminLogin', [AdminController::class, 'loginAdmin']);
//////////////////////////////////////////////////////////private Route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('customer', [CustomerController::class, 'getDataCustomer']);
    Route::get('dataSalon/{categoryId}', [SalonController::class, 'getSalon']);
    Route::get('salon/{salonId}', [SalonController::class, 'getSalonById']);
    Route::post('addComment', [CommentController::class, 'addComment']);
    Route::get('comments/{salonId}', [CommentController::class, 'getComments']);
    Route::get('getDiscount/{salonId}', [Discountcontroller::class, 'getDiscountSalon']);
    Route::get('salonTime', [BookingControllerr::class, 'availableTimes']);
    Route::post('showInfoBooking', [BookingControllerr::class, 'infoBooking']);
    Route::post('booking', [BookingControllerr::class, 'store']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('addCategory', [ServiceController::class, 'addCategory']);
    Route::post('barberService', [ServiceController::class, 'barberService']);
    Route::post('addSalon', [SalonController::class, 'addSalon']);
    Route::get('getSalons', [SalonController::class, 'getSalons']);
    Route::post('uploadSalon/{id}', [SalonController::class, 'addImageSalon']);
    Route::post('addAdmin', [AdminController::class, 'createAdmin']);
    Route::get('getDataAdmin', [AdminController::class, 'getAdmins']);
    Route::patch('editAdmin/{id}', [AdminController::class, 'EditAdmin']);
    Route::patch('deleteAdmin/{id}', [AdminController::class, 'delete']);
    Route::get('adminSearch', [AdminController::class, 'searchAdmin']);
    Route::get('category', [ServiceController::class, 'getCategory']);
    Route::patch('editService/{id}', [ServiceController::class, 'editService']);
    Route::patch('deletService/{id}', [ServiceController::class, 'deletService']);
    Route::post('addDiscount/{salonId}', [Discountcontroller::class, 'addDiscount']);
    Route::get('commentPending/{salonId}', [CommentController::class, 'getCommentPending']);
    Route::delete('rejectComment/{id}', [CommentController::class, 'rejectionComment']);
    Route::post('imageGallery/{salonId}', [SalonController::class, 'imageGallerySalon']);
    Route::put('accept/{id}', [CommentController::class, 'acceptComment']);
    Route::post('addRole', [CustomerController::class, 'addRole']);
    Route::post('addBarber', [SalonController::class, 'addBarber']);
    Route::post('addService', [ServiceController::class, 'addService']);
    Route::get('serviceAll', [ServiceController::class, 'dataServiceAll']);
    Route::get('service', [ServiceController::class, 'dataService']);
    Route::get('Booking/{salonId}', [BookingControllerr::class, "getBooking"]);
    Route::post('salonService/{salon_id}', [ServiceController::class, 'salonService']);
});




