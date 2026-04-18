<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        $student = Student::where('email', $user->email)->first();
        $onboardingCompleted = $student !== null && ! empty($student->preferences);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'onboarding_completed' => $onboardingCompleted,
        ]);
    }
}
