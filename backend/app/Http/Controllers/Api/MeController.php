<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('email', $user->email)->first();
        $onboardingCompleted = $student !== null && ! empty($student->preferences);

        return response()->json([
            'user' => new UserResource($user),
            'onboarding_completed' => $onboardingCompleted,
        ]);
    }
}
