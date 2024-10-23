<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:api')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('product.index');
    Route::post('/products', [ProductController::class, 'store'])->name('product.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
});

