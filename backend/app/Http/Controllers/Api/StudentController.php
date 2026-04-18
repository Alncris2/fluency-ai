<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferencesRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;

class StudentController extends Controller
{
    public function updatePreferences(UpdatePreferencesRequest $request, Student $student): StudentResource
    {
        $student->update(['preferences' => $request->validated()]);

        Cache::forget("onboarding_progress:{$request->user()->id}");

        return new StudentResource($student->fresh());
    }
}
