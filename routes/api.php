<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);

// Projects (read-only public)
Route::get('/projects',      [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);

// Skills (read-only public)
Route::get('/skills', [SkillController::class, 'index']);

// Contact form submission
Route::post('/contact', [ContactController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Protected Routes (JWT)
|--------------------------------------------------------------------------
*/

Route::middleware('jwt.auth')->group(function () {

    // ── Auth ──────────────────────────────────────────────────────────────

    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ── Projects (admin CRUD) ─────────────────────────────────────────────

    Route::post('/projects',      [ProjectController::class, 'store']);
    Route::post('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // ── Skills (admin CRUD) ───────────────────────────────────────────────

    Route::post('/skills',       [SkillController::class, 'store']);
    Route::put('/skills/{id}',   [SkillController::class, 'update']);
    Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

    // ── Contact messages (admin read) ─────────────────────────────────────

    Route::get('/contact',  [ContactController::class, 'index']);
    Route::get('/messages', [ContactController::class, 'index']); // alias
});
