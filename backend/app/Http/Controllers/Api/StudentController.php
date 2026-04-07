<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferencesRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;

class StudentController extends Controller
{
    public function updatePreferences(UpdatePreferencesRequest $request, Student $student): StudentResource
    {
        $student->update(['preferences' => $request->validated()]);

        return new StudentResource($student->fresh());
    }
}
