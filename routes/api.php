<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
Route::post('User/Login', [AuthController::class,'login'])->name('user.login');
Route::post('User/Register', [AuthController::class,'register'])->name('user.register');

Route::group(['middleware'=>['auth:api']], function(){
    
    // Public routes for authonticated user
    Route::get('User/Logout', [AuthController::class,'logout'])->name('user.logout');
    Route::get('User/Info', [AuthController::class,'userInformation'])->name('user.information');
    Route::put('User/Update', [AuthController::class,'update'])->name('user.update');

    
    
    // Admin's route
    Route::middleware(['is_admin'])->group(function(){
        // 
        Route::get('v1/allUser',[AuthController::class,'allUser'])->name('allUser');
    });

    // User's route
    Route::middleware(['is_user'])->group(function(){
        // Route
    });
    
});
