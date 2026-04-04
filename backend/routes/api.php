<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\QuizController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn (): JsonResponse => response()->json([
    'status' => 'ok',
    'service' => 'fluency-ai-backend',
    'timestamp' => now()->toISOString(),
]));

Route::prefix('v1')->group(function (): void {
    Route::prefix('students/{student}')->group(function (): void {
        Route::post('chat', [ChatController::class, 'chat']);
        Route::post('chat/stream', [ChatController::class, 'stream']);
        Route::post('chat/voice', [ChatController::class, 'voiceChat']);
        Route::get('chat/voice/greeting', [ChatController::class, 'voiceGreeting']);
    });

    Route::post('quiz/{quiz}/answer', [QuizController::class, 'answer']);
});
