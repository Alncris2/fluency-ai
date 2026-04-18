<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse
    {
        [$user, $student] = DB::transaction(function () use ($request) {
            $user = User::create($request->only('name', 'email', 'password'));
            $student = Student::create([
                'name' => $request->name,
                'email' => $request->email,
                'level' => 'beginner',
            ]);

            return [$user, $student];
        });

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'student_id' => $student->id,
            'onboarding_completed' => false,
        ], 201);
    }
}
