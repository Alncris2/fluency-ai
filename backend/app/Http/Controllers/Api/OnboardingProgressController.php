<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OnboardingProgressController extends Controller
{
    private const TTL_SECONDS = 86400; // 1 day

    public function show(Request $request): JsonResponse
    {
        $progress = Cache::get($this->cacheKey($request));

        return response()->json(['progress' => $progress]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate(['progress' => ['required', 'array']]);

        Cache::put(
            $this->cacheKey($request),
            $request->input('progress'),
            self::TTL_SECONDS
        );

        return response()->json(['message' => 'Progress saved.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        Cache::forget($this->cacheKey($request));

        return response()->json(['message' => 'Progress cleared.']);
    }

    private function cacheKey(Request $request): string
    {
        return "onboarding_progress:{$request->user()->id}";
    }
}

