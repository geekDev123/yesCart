<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\IndexController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\CartController;
Route::group([

    'middleware'=>['jwt.verify','customer'],
    //'middleware' => 'auth.jwt',
    'prefix' => 'customer',
    'as' => 'customer.'

], function ($router) {
        Route::group(['prefix' => 'butchers','as' => 'butchers.'], function ($router) {
            Route::get('/nearBy', [IndexController::class, 'get_nearby_butchers']);
            Route::get('/get_butcher', [IndexController::class, 'get_butcher_by_id']);
        });

        Route::group(['prefix' => 'orders','as' => 'orders.'], function ($router) {
            Route::post('/store', [OrderController::class, 'store'])->name('store');
            Route::get('/list', [OrderController::class, 'list'])->name('list');
            Route::get('/payment_success', [OrderController::class, 'payment_success'])->name('payment_success');
            Route::get('/cancel_payment', [OrderController::class, 'cancel_payment'])->name('cancel_payment');
            Route::post('/cancel_subscription/{id}', [OrderController::class, 'cancel_subscription'])->name('cancel_subscription');
        });

        Route::group(['prefix' => 'payment', 'as' => 'payment.'], function($router){
            Route::post('/store', [PaymentController::class, 'store'])->name('store');
            Route::post('/stripePost', [PaymentController::class, 'stripePost'])->name('stripePost');
            Route::get('/plans', [PaymentController::class, 'plans'])->name('plans');
        });

        Route::group(['prefix' => 'cart', 'as' => 'cart.'], function($router){
            Route::post('/addToCart', [CartController::class, 'addToCart'])->name('addToCart');
            Route::post('/update', [CartController::class, 'update'])->name('update');
            Route::get('/list', [CartController::class, 'list'])->name('list');
            Route::post('/delete', [CartController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'products', 'as' => 'products.'], function($router){
            Route::get('/search', [IndexController::class, 'search'])->name('search');
        });
       /*  Route::group(['prefix' => 'packages', 'as' => 'packages.'], function($router){
            Route::get('/search', [IndexController::class, 'search'])->name('search');
        }); */
});

?>