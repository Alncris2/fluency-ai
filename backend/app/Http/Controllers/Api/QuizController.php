<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnswerQuizRequest;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    public function answer(AnswerQuizRequest $request, Quiz $quiz): JsonResponse
    {
        if ($quiz->status === 'answered') {
            return response()->json(['message' => 'Quiz já foi respondido'], 422);
        }

        $studentAnswer = $request->input('answer');
        $correct = strtolower(trim($studentAnswer)) === strtolower(trim($quiz->correct_answer));
        $score = $correct ? 1.0 : 0.0;

        $quiz->update([
            'student_answer' => $studentAnswer,
            'score' => $score,
            'status' => 'answered',
            'answered_at' => now(),
        ]);

        $payload = [
            'correct' => $correct,
            'score' => $score,
            'explanation' => $quiz->explanation,
        ];

        if (! $correct) {
            $payload['correct_answer'] = $quiz->correct_answer;
        }

        return response()->json($payload);
    }
}
