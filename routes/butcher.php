<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Butcher\IndexController;
use App\Http\Controllers\Butcher\ProductController;
use App\Http\Controllers\Butcher\OrderController;
Route::group([
    'middleware' => ['jwt.verify','butcher'],
    //'middleware' => 'auth.jwt',
    'prefix' => 'butcher',
    'as' => 'butcher.'

], function ($router) {
  
        Route::group(['prefix' => 'products','as' => 'products.'], function ($router) {
            Route::get('/list', [IndexController::class, 'list'])->name('list');
            Route::post('/store', [ProductController::class, 'store'])->name('store');
            Route::post('/update', [ProductController::class, 'update'])->name('update');
            Route::post('/delete', [ProductController::class, 'delete'])->name('delete');
            Route::get('/show/{id}', [ProductController::class, 'show'])->name('show');
        });
        Route::group(['prefix' => 'orders','as' => 'orders.'], function ($router) {
            Route::get('/list', [OrderController::class, 'list'])->name('list');
        });
  
});

?>