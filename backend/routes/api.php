<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
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
    
    // Admin only routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Admin routes will be added here
    });
});
