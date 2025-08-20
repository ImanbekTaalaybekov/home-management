<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DebtImportController;
use App\Http\Controllers\InputDebtDataController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PrivacyDocumentController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth', [AuthController::class, 'auth']);
Route::post('/verify-sms', [AuthController::class, 'verifySmsCode']);
Route::post('/register', [AuthController::class, 'register']);
Route::put('/user', [AuthController::class, 'update'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/user-fcm-token', [AuthController::class, 'updateFcmToken'])->middleware('auth:sanctum');
Route::post('/user-fcm-token-remove', [AuthController::class, 'removeFcmToken'])->middleware('auth:sanctum');

Route::get('/complaints', [ComplaintController::class, 'index'])->middleware('auth:sanctum');
Route::post('/complaints', [ComplaintController::class, 'store'])->middleware('auth:sanctum');
Route::get('/complaints/{id}', [ComplaintController::class, 'show']);

Route::get('/suggestions', [SuggestionController::class, 'index']);
Route::post('/suggestions', [SuggestionController::class, 'store']);

Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth:sanctum');
Route::get('/notifications/{id}', [NotificationController::class, 'show']);
Route::post('/notifications', [NotificationController::class, 'store']);
Route::post('/notifications/status', [NotificationController::class, 'status'])->middleware('auth:sanctum');

Route::get('/debts', [DebtController::class, 'getUserDebts'])->middleware('auth:sanctum');
Route::get('/debts/{id}', [DebtController::class, 'getSingleDebt']);
Route::get('/debts-sum', [DebtController::class, 'getUserDebtsSum'])->middleware('auth:sanctum');

Route::post('/service-requests', [ServiceRequestController::class, 'store']);
Route::get('/service-requests-categories', [ServiceRequestController::class, 'getCategories']);

Route::get('/complaints', [ComplaintController::class, 'index'])->middleware('auth:sanctum');
Route::get('/complaints/{id}', [ComplaintController::class, 'show']);
Route::post('/complaints', [ComplaintController::class, 'store']);

Route::post('/suggestions', [SuggestionController::class, 'store']);

Route::get('/polls', [PollController::class, 'index'])->middleware('auth:sanctum');
Route::get('/polls/latest', [PollController::class, 'showLatestPoll'])->middleware('auth:sanctum');
Route::post('/polls', [PollController::class, 'store'])->middleware('auth:sanctum');
Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->middleware('auth:sanctum');
Route::get('/polls/{poll}', [PollController::class, 'show'])->middleware('auth:sanctum');
Route::get('/polls/protocol/{poll}', [PollController::class, 'generateProtocol']);

Route::get('/announcements', [AnnouncementController::class, 'index'])->middleware('auth:sanctum');
Route::get('/announcements/own', [AnnouncementController::class, 'showOwn'])->middleware('auth:sanctum');
Route::get('/announcements/{id}', [AnnouncementController::class, 'show'])->middleware('auth:sanctum');
Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/upload-debt-data-alseco', [InputDebtDataController::class, 'uploadAlseco']);
Route::post('/upload-debt-data-ivc', [InputDebtDataController::class, 'uploadIvc']);

Route::post('/debt-import', [DebtImportController::class, 'importDebt']);

Route::post('/knowledge-base/categories', [KnowledgeBaseController::class, 'storeCategory']);
Route::get('/knowledge-base/categories', [KnowledgeBaseController::class, 'indexCategories']);
Route::post('/knowledge-base/articles', [KnowledgeBaseController::class, 'storeArticle']);
Route::get('/knowledge-base/articles', [KnowledgeBaseController::class, 'indexArticles']);
Route::get('/knowledge-base/articles/{id}', [KnowledgeBaseController::class, 'showArticle']);

Route::get('/company-documents/user-agreement', [PrivacyDocumentController::class, 'getUserAgreement']);
Route::get('/company-documents/privacy-policy', [PrivacyDocumentController::class, 'getPrivacyPolicy']);