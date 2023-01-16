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

});

Route::prefix('patients')->group(function () {
    Route::group(['middleware' => ['auth:api', 'role:Patients']], function () {
        Route::get('/todos', [App\Http\Controllers\Patients\TodoController::class, 'index']);
        Route::post('/todos/create', [App\Http\Controllers\Patients\TodoController::class, 'create']);
        Route::put('/todos/{todo}/update', [App\Http\Controllers\Patients\TodoController::class, 'update']);
    });
});
