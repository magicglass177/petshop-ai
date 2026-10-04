<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AiController;


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

Route::get('/status', function () {
    return response()->json([
        'success' => true,
        'message' => 'Pet Shop API berhasil terhubung',
        'application' => 'Pet Shop AI',
        'backend' => 'Laravel',
    ]);
});


/*
|--------------------------------------------------------------------------
| AUTH PUBLIC
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| PUBLIC PRODUCT CATEGORIES
|--------------------------------------------------------------------------
*/

Route::get(
    '/categories',
    [ProductCategoryController::class, 'index']
);

Route::get(
    '/categories/{category}',
    [ProductCategoryController::class, 'show']
);


/*
|--------------------------------------------------------------------------
| PUBLIC PRODUCTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/products',
    [ProductController::class, 'index']
);

Route::get(
    '/products/{product}',
    [ProductController::class, 'show']
);


/*
|--------------------------------------------------------------------------
| PUBLIC SERVICES
|--------------------------------------------------------------------------
*/

Route::get(
    '/services',
    [ServiceController::class, 'index']
);

Route::get(
    '/services/{service}',
    [ServiceController::class, 'show']
);

Route::post(
    '/ai/analyze',
    [AiController::class, 'analyze']
);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get(
        '/me',
        [AuthController::class, 'me']
    );

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    

    

});

/*
|--------------------------------------------------------------------------
| CUSTOMER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'customer'])
    ->prefix('customer')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PROFILE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            [CustomerController::class, 'profile']
        );

        Route::put(
            '/profile',
            [CustomerController::class, 'updateProfile']
        );

        Route::get(
    '/dashboard',
    [CustomerController::class, 'dashboard']
);

Route::post(
    '/ai/analyze',
    [AiController::class, 'analyzeWithPet']
);

Route::get(
    '/ai/history',
    [AiController::class, 'history']
);

Route::get(
    '/ai/history/{analysis}',
    [AiController::class, 'historyDetail']
);


        /*
        |--------------------------------------------------------------------------
        | PETS
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/pets',
            [PetController::class, 'index']
        );

        Route::post(
            '/pets',
            [PetController::class, 'store']
        );

        Route::get(
            '/pets/{pet}',
            [PetController::class, 'show']
        );

        Route::put(
            '/pets/{pet}',
            [PetController::class, 'update']
        );

        Route::delete(
            '/pets/{pet}',
            [PetController::class, 'destroy']
        );

    });


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get(
    '/ai/monitoring',
    [AiController::class, 'adminMonitoring']
);

        Route::get(
    '/dashboard',
    [AdminDashboardController::class, 'index']
);

Route::get(
    '/services',
    [ServiceController::class, 'adminIndex']
);

Route::get(
    '/customers',
    [AdminCustomerController::class, 'index']
);

Route::get(
    '/customers/{customer}',
    [AdminCustomerController::class, 'show']
);


        /*
        |--------------------------------------------------------------------------
        | PRODUCT CATEGORIES
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/categories',
            [ProductCategoryController::class, 'store']
        );

        Route::put(
            '/categories/{category}',
            [ProductCategoryController::class, 'update']
        );

        Route::delete(
            '/categories/{category}',
            [ProductCategoryController::class, 'destroy']
        );


        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */
        Route::get(
    '/products',
    [ProductController::class, 'adminIndex']
);

        Route::post(
            '/products',
            [ProductController::class, 'store']
        );

        Route::put(
            '/products/{product}',
            [ProductController::class, 'update']
        );

        Route::delete(
            '/products/{product}',
            [ProductController::class, 'destroy']
        );


        /*
        |--------------------------------------------------------------------------
        | SERVICES
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/services',
            [ServiceController::class, 'store']
        );

        Route::put(
            '/services/{service}',
            [ServiceController::class, 'update']
        );

        Route::delete(
            '/services/{service}',
            [ServiceController::class, 'destroy']
        );

    });