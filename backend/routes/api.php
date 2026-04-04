<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn (): JsonResponse => response()->json([
    'status' => 'ok',
    'service' => 'fluency-ai-backend',
    'timestamp' => now()->toISOString(),
]));
