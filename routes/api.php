<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
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


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);  
Route::group(['middleware' => 'jwt.verify'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user_profile', [AuthController::class, 'userProfile']);
});

Route::group(['prefix' => 'categories','as' => 'categories.'], function ($router) {
    Route::get('/list', [CategoryController::class, 'list'])->name('list');
});
