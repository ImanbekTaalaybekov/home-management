<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\InputDebtDataController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function () {
    Route::post('auth', [AuthController::class, 'auth']);
    Route::post('register', [AuthController::class, 'register']);
    Route::put('user', [AuthController::class, 'update'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/complaints', [ComplaintController::class, 'index']);
Route::post('/complaints', [ComplaintController::class, 'store']);

Route::get('/suggestions', [SuggestionController::class, 'index']);
Route::post('/suggestions', [SuggestionController::class, 'store']);

Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications', [NotificationController::class, 'store']);

Route::get('/debts', [DebtController::class, 'index']);
Route::post('/debts', [DebtController::class, 'store']);
Route::post('/debts/upload', [DebtController::class, 'upload']);

Route::get('/service-requests', [ServiceRequestController::class, 'index']);
Route::post('/service-requests', [ServiceRequestController::class, 'store']);

Route::get('/complaints', [ComplaintController::class, 'index']);
Route::post('/complaints', [ComplaintController::class, 'store']);

Route::get('/suggestions', [SuggestionController::class, 'index']);
Route::post('/suggestions', [SuggestionController::class, 'store']);

Route::get('/polls', [PollController::class, 'index']);
Route::post('/polls/{poll}/vote', [PollController::class, 'vote']);

Route::get('/announcements', [AnnouncementController::class, 'index']);

Route::post('/upload-debt-data', [InputDebtDataController::class, 'upload']);