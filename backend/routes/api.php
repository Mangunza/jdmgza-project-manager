<?php

use App\Domains\Auth\Controllers\AuthController;
use App\Domains\Auth\Controllers\AuthorizationTestController;
use App\Domains\Projects\Controllers\ProjectController;
use App\Domains\Projects\Controllers\ProjectServiceController;
use App\Domains\Services\Controllers\ServiceController;
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


/*
|--------------------------------------------------------------------------
| Projects
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->prefix('projects')
    ->group(function () {
        Route::get('/', [ProjectController::class, 'index'])
            ->middleware('permission:projects.view');

        Route::post('/', [ProjectController::class, 'store'])
            ->middleware('permission:projects.create');

        Route::get('/{project}', [ProjectController::class, 'show'])
            ->middleware('permission:projects.view');

        Route::put('/{project}', [ProjectController::class, 'update'])
            ->middleware('permission:projects.update');

        Route::patch('/{project}', [ProjectController::class, 'update'])
            ->middleware('permission:projects.update');

        Route::delete('/{project}', [ProjectController::class, 'destroy'])
            ->middleware('permission:projects.delete');

        Route::post('/{project}/services', [ProjectServiceController::class, 'store'])
            ->middleware('permission:projects.update');

        Route::get('/{project}/services', [ProjectServiceController::class, 'index'])
            ->middleware('permission:projects.view');

        Route::get('/{project}/services/{projectService}', [ProjectServiceController::class, 'show'])
            ->middleware('permission:projects.view');

        Route::put('/{project}/services/{projectService}', [ProjectServiceController::class, 'update'])
            ->middleware('permission:projects.update');

        Route::patch('/{project}/services/{projectService}', [ProjectServiceController::class, 'update'])
            ->middleware('permission:projects.update');

        Route::delete('/{project}/services/{projectService}', [ProjectServiceController::class, 'destroy'])
            ->middleware('permission:projects.update');
    });

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->prefix('services')
    ->group(function () {
        Route::get('/', [ServiceController::class, 'index'])
            ->middleware('permission:services.view');

        Route::post('/', [ServiceController::class, 'store'])
            ->middleware('permission:services.create');

        Route::get('/{service}', [ServiceController::class, 'show'])
            ->middleware('permission:services.view');

        Route::put('/{service}', [ServiceController::class, 'update'])
            ->middleware('permission:services.update');

        Route::patch('/{service}', [ServiceController::class, 'update'])
            ->middleware('permission:services.update');

        Route::patch('/{service}/activate', [ServiceController::class, 'activate'])
            ->middleware('permission:services.update');

        Route::patch('/{service}/deactivate', [ServiceController::class, 'deactivate'])
            ->middleware('permission:services.update');
    });
