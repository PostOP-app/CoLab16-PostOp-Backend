<?php

use Illuminate\Support\Facades\Route;

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

Route::post('/register', [App\Http\Controllers\Patients\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Patients\AuthController::class, 'login']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
    Route::middleware('auth:api')->get('/user', function () {

    });
});
