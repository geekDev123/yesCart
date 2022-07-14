<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\IndexController;
use App\Http\Controllers\Agent\PackageController;
Route::group([

    'middleware'=>['jwt.verify','agent'],
    'prefix' => 'agent',
    'as' => 'agent.'

], function ($router) {
        Route::group(['prefix' => 'butchers','as' => 'butchers.'], function ($router) {
            Route::get('/nearBy', [IndexController::class, 'get_nearby_butchers']);
        });

        Route::group(['prefix' => 'packages','as' => 'packages.'], function ($router) {
            Route::post('/store', [PackageController::class, 'store'])->name('store');
            Route::get('/list', [PackageController::class, 'list'])->name('list');
            Route::post('/update', [PackageController::class, 'update'])->name('update');
            Route::post('/delete', [PackageController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'products', 'as' => 'products.'], function($router){
            Route::get('/search', [IndexController::class, 'search'])->name('search');
        });
});

?>