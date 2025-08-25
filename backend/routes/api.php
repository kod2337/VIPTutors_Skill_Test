<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Task management routes
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/reorder', [TaskController::class, 'reorder']);
    Route::patch('/tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus']);
    Route::get('/tasks-statistics', [TaskController::class, 'statistics']);
    
    // Advanced search routes
    Route::get('/tasks-search-suggestions', [TaskController::class, 'searchSuggestions']);
    Route::get('/tasks-filter-options', [TaskController::class, 'filterOptions']);
    
    // Admin only routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Dashboard and statistics
        Route::get('/dashboard-stats', [AdminController::class, 'getDashboardStats']);
        Route::get('/task-statistics', [AdminController::class, 'getTaskStatistics']);
        Route::get('/top-performers', [AdminController::class, 'getTopPerformers']);
        
        // User management
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{user}', [AdminController::class, 'getUserDetails']);
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole']);
        
        // Task management (admin privileges)
        Route::delete('/tasks/{task}', [AdminController::class, 'deleteTask']);
    });
});

