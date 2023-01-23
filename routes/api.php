<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServicemed_provider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::post('/register', [App\Http\Controllers\Patients\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Patients\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Patients\AuthController::class, 'logout']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout']);
});

Route::prefix('patient')->group(function () {
    Route::group(['middleware' => ['auth:api', 'role:patient']], function () {
        Route::get('/todos', [App\Http\Controllers\Shared\TodoController::class, 'fetchPatientTodos']);
    });
});

Route::prefix('todos')->group(function () {
    $roles = ['patient', 'med_provider'];
    Route::group(['middleware' => ['auth:api', 'role:' . implode('|', $roles)]], function () {
        Route::patch('/{todo}/complete', [App\Http\Controllers\Shared\TodoController::class, 'completeTodo']);
        Route::put('/{todo}/archive', [App\Http\Controllers\Shared\TodoController::class, 'archive']);
        Route::put('/{slug}/restore', [App\Http\Controllers\Shared\TodoController::class, 'restore']);
        Route::delete('/{slug}/delete', [App\Http\Controllers\Shared\TodoController::class, 'destroy']);
    });
});

Route::prefix('med_provider')->group(function () {
    // Route::post('/register', [App\Http\Controllers\med_provider\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\MedProviders\AuthController::class, 'login']);

    Route::group(['middleware' => ['auth:api', 'role:med_provider']], function () {
        Route::get('/all-patient', [App\Http\Controllers\Shared\TodoController::class, 'fetchPatient']);
        Route::get('/todos', [App\Http\Controllers\Shared\TodoController::class, 'index']);
        Route::post('/todos/create', [App\Http\Controllers\Shared\TodoController::class, 'create']);
        Route::put('/todos/{todo}/update', [App\Http\Controllers\Shared\TodoController::class, 'update']);
    });
});

Route::prefix('messages')->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('/', [App\Http\Controllers\Shared\MessageController::class, 'getAllMessages']);
        Route::post('{id}/send', [App\Http\Controllers\Shared\MessageController::class, 'sendMessage']);
        Route::get('{id}/fetch', [App\Http\Controllers\Shared\MessageController::class, 'fetchMessages']);
        Route::get('unread', [App\Http\Controllers\Shared\MessageController::class, 'getUnreadMessages']);
        Route::patch('/{id}/mark-as-read', [App\Http\Controllers\Shared\MessageController::class, 'markMessageAsRead']);
        Route::delete('/{id}/delete', [App\Http\Controllers\Shared\MessageController::class, 'deleteMessage']);
    });
});
