<?php

use App\Domains\Auth\Controllers\AuthController;
use App\Domains\Auth\Controllers\AuthorizationTestController;
use App\Domains\Users\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| Authorization Test
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->get(
        '/authorization-test/users-create',
        [AuthorizationTestController::class, 'usersCreate']
    )
    ->middleware('permission:users.create');

/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->prefix('users')
    ->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:users.view');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:users.create');

        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('permission:users.view');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.update');

        Route::patch('/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.update');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete');
    });
