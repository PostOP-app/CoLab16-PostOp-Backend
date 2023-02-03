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

Route::prefix('admin')->group(function () {
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
    Route::middleware(['auth:api', 'role:admin'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout']);
    });
});

Route::prefix('patient')->group(function () {
    Route::group(['middleware' => ['auth:api', 'role:patient']], function () {
        Route::get('/todos', [App\Http\Controllers\Shared\TodoController::class, 'fetchPatientTodos']);
        Route::post('/logout', [App\Http\Controllers\Patients\AuthController::class, 'logout']);
    });
});

// todo routes
Route::prefix('todos')->group(function () {
    $roles = ['patient', 'med_provider'];
    Route::group(['middleware' => ['auth:api', 'role:' . implode('|', $roles)]], function () {
        Route::get('/{todo}', [App\Http\Controllers\Shared\TodoController::class, 'fetchTodo']);
        Route::patch('/{todo}/complete', [App\Http\Controllers\Shared\TodoController::class, 'completeTodo']);
        Route::put('/{todo}/archive', [App\Http\Controllers\Shared\TodoController::class, 'archive']);
        Route::put('/{slug}/restore', [App\Http\Controllers\Shared\TodoController::class, 'restore']);
        Route::delete('/{slug}/delete', [App\Http\Controllers\Shared\TodoController::class, 'destroy']);

    });
});

// recovery plan routes
Route::prefix('recovery-plans')->group(function () {
    $roles = ['patient', 'med_provider'];
    Route::group(['middleware' => ['auth:api', 'role:' . implode('|', $roles)]], function () {
        Route::get('/', [App\Http\Controllers\Shared\RecoveryPlanController::class, 'fetchRecoveryPlans']);
        Route::get('/{recoveryPlan}', [App\Http\Controllers\Shared\RecoveryPlanController::class, 'fetchRecoveryPlan']);
    });
});

Route::prefix('med_provider')->group(function () {
    // Route::post('/register', [App\Http\Controllers\med_provider\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\MedProviders\AuthController::class, 'login']);

    Route::group(['middleware' => ['auth:api', 'role:med_provider']], function () {
        // patients routes
        Route::get('/all-patients', [App\Http\Controllers\Shared\TodoController::class, 'fetchPatients']);

        // todo routes
        Route::prefix('todos')->group(function () {
            Route::get('/', [App\Http\Controllers\Shared\TodoController::class, 'index']);
            Route::post('/create', [App\Http\Controllers\Shared\TodoController::class, 'create']);
            Route::put('/{todo}/update', [App\Http\Controllers\Shared\TodoController::class, 'update']);
        });

        // logout route
        Route::post('/logout', [App\Http\Controllers\MedProviders\AuthController::class, 'logout']);

        // recovery plan routes
        Route::prefix('recovery-plans', function () {
            Route::post('/create', [App\Http\Controllers\Shared\RecoveryPlanController::class, 'createRecoveryPlan']);
            Route::put('/{recoveryPlan}/update', [App\Http\Controllers\Shared\RecoveryPlanController::class, 'updateRecoveryPlan']);
            Route::delete('/{recoveryPlan}/delete', [App\Http\Controllers\Shared\RecoveryPlanController::class, 'deleteRecoveryPlan']);
        });
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
