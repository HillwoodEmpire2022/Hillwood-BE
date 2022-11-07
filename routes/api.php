<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\SubsribeController;

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
    Route::put('User/Update', [AuthController::class,'update'])->name('user.update');
    Route::get('User/Info', [AuthController::class,'userInformation'])->name('user.information');
    Route::get('User/Logout', [AuthController::class,'logout'])->name('user.logout');

    
    
    // Admin's route
    Route::middleware(['is_admin'])->group(function(){
        // 
        Route::get('User/AllUser',[AuthController::class,'allUser'])->name('allUser');

        // channel route
        Route::get('Channel/All', [ChannelController::class,'getAllChannels'])->name('channel.all');
        Route::get('Channel/Id/{id}', [ChannelController::class,'getChannelById'])->name('channel.id');
    });

    // User's route
    Route::middleware(['is_user'])->group(function(){
        // channel
        Route::get('Channel/User', [ChannelController::class,'getChannelByUser'])->name('channel.user');
        Route::post('Channel/Create', [ChannelController::class,'createChannel'])->name('create.channel');
        Route::post('Channel/Update/{id}', [ChannelController::class,'channelUpdate'])->name('update.channel');
        
        
    });
    
    // Subscribe channel
    Route::post('Subscribe/Channel/{id}', [SubsribeController::class,'createSubscribe'])->name('channel.subscribe');
    Route::post('Unsubscribe/Channel/{id}', [SubsribeController::class,'createUnsubscribe'])->name('channel.subscribe');
    Route::get('Channel/Subscribers/{id}', [SubsribeController::class,'getChannelSubscribers'])->name('channel.subscribers');

    // Channels route
    Route::delete('Channel/Delete/{id}', [ChannelController::class,'channelDelete'])->name('delete.channel');
    
});
