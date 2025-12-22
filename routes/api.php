<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BrandsController;
use App\Http\Controllers\Api\CertificationController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CoverImageController;
use App\Http\Controllers\Api\CsrController;
use App\Http\Controllers\Api\DimensionController;
use App\Http\Controllers\Api\MasterCategoryController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TeakImageController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Health check endpoint for Render.com (outside v1 prefix)
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
        ], 503);
    }
});

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Endpoint Publik
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::get('csrs', [CsrController::class, 'index']);
    Route::get('csrs/{id}', [CsrController::class, 'show']);
    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/top-news', [NewsController::class, 'topNews']);
    Route::get('news/{id}', [NewsController::class, 'show']);
    Route::get('brands', [BrandsController::class, 'index']);
    Route::get('brands/{id}', [BrandsController::class, 'show']);
    Route::get('certifications', [CertificationController::class, 'index']);
    Route::get('certifications/{id}', [CertificationController::class, 'show']);
    Route::get('master-categories', [MasterCategoryController::class, 'index']);
    Route::get('master-categories/{id}', [MasterCategoryController::class, 'show']);
    Route::get('dimensions', [DimensionController::class, 'index']);
    Route::get('dimensions/{id}', [DimensionController::class, 'show']);

    // Banners - public endpoint for collections page
    Route::get('banners', [BannerController::class, 'index']);

    // Contact form - public endpoint with stricter rate limit (5 per minute)
    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:5,1');

    // Endpoint Terlindungi (Butuh Token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Super Admin only routes
        Route::middleware('role:Super Admin')->group(function () {
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('users', UserController::class);
        });

        // Admin contact messages management (all authenticated users)
        Route::get('admin/contacts/stats', [ContactController::class, 'stats']);
        Route::get('admin/contacts', [ContactController::class, 'index']);
        Route::get('admin/contacts/{id}', [ContactController::class, 'show']);
        Route::put('admin/contacts/{id}', [ContactController::class, 'update']);
        Route::delete('admin/contacts/{id}', [ContactController::class, 'destroy']);


        // Admin banners management (all authenticated users)
        Route::get('admin/banners', [BannerController::class, 'adminIndex']);
        Route::post('admin/banners', [BannerController::class, 'store']);
        Route::get('admin/banners/{id}', [BannerController::class, 'show']);
        Route::post('admin/banners/{id}', [BannerController::class, 'update']);
        Route::delete('admin/banners/{id}', [BannerController::class, 'destroy']);

        // Operasi CRUD kecuali read (sudah publik)
        Route::apiResource('master-categories', MasterCategoryController::class)->except(['index', 'show']);
        Route::apiResource('dimensions', DimensionController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        Route::apiResource('csrs', CsrController::class)->except(['index', 'show']);
        Route::apiResource('news', NewsController::class)->except(['index', 'show']);
        Route::apiResource('certifications', CertificationController::class)->except(['index', 'show']);
        Route::apiResource('brands', BrandsController::class)->except(['index', 'show']);

        Route::delete('product-images/{id}', [ProductImageController::class, 'destroy']);
        Route::delete('product-images/{id}/force', [ProductImageController::class, 'forceDestroy']);
        Route::delete('teak-images/{id}', [TeakImageController::class, 'destroy']);
        Route::delete('teak-images/{id}/force', [TeakImageController::class, 'forceDestroy']);
        Route::delete('cover-images/{id}', [CoverImageController::class, 'destroy']);
        Route::delete('cover-images/{id}/force', [CoverImageController::class, 'forceDestroy']);

        // Upload endpoints for editor images
        Route::post('upload/image', [UploadController::class, 'uploadImage']);
        Route::post('upload/images', [UploadController::class, 'uploadMultiple']);
        Route::delete('upload/image', [UploadController::class, 'deleteImage']);
    });

    Route::get('/ping', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'Laravel connected',
        ]);
    });
});

