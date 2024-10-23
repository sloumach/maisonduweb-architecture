<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

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
Route::post('/register', [AuthController::class, 'register'])->name('user.register');
Route::post('/login', [AuthController::class, 'login'])->name('user.login');
Route::middleware('auth:api')->get('/user', [AuthController::class, 'getUser'])->name('user.get');
// routes/api.php
Route::middleware('auth:api', 'check.role:admin')->group(function () {
    Route::post('/users/{user}/assign-role', [AdminController::class, 'assignRole']);
    Route::post('/users/{user}/revoke-role', [AdminController::class, 'revokeRole']);
    Route::post('/users/{role}/assign-permission', [AdminController::class, 'assignPermissionToRole']);
    Route::post('/users/{role}/revoke-permission', [AdminController::class, 'revokePermissionFromRole']);
});


