<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

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

Route::prefix('carts')->group(function () {
    Route::controller(CartController::class)->group(function () {
        Route::get('/', 'index')->name('cart.index');
        Route::post('/', 'store')->name('cart.store');
        Route::delete('/{cartItemId}', 'delete')->name('cart.delete');
        Route::get('/items/deleted', 'getDeletedItems')->name('cart.getDeletedItems');
    });
});
