<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CustomerController;
Route::post('customer',[CustomerController::class,'createCustomer']);
Route::post('login',[CustomerController::class,'loginCustomer']);
Route::post('sendCode',[CustomerController::class,'sendOtp']);
Route::post('verifyCode',[CustomerController::class,'verifyCode']);
Route::put('changePassword',[CustomerController::class,'changePassword']);
Route::get('customer',[CustomerController::class,'getDataCustomer']);

