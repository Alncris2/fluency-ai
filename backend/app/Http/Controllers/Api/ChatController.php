<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\EnglishTeacherAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessageRequest;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function chat(ChatMessageRequest $request, Student $student): JsonResponse
    {
        $agent = new EnglishTeacherAgent(
            student: $student,
            sessionId: $request->input('session_id'),
        );

        $response = $agent->prompt($request->input('message'));

        $agent->appendToHistory('user', $request->input('message'));
        $agent->appendToHistory('assistant', (string) $response);

        return response()->json([
            'message' => (string) $response,
            'session_id' => $request->input('session_id'),
        ]);
    }

    public function stream(ChatMessageRequest $request, Student $student): StreamedResponse
    {
        $message = $request->input('message');
        $sessionId = $request->input('session_id');

        $agent = new EnglishTeacherAgent(student: $student, sessionId: $sessionId);

        return response()->stream(function () use ($agent, $message): void {
            $fullResponse = '';

            foreach ($agent->stream($message) as $chunk) {
                $fullResponse .= $chunk;
                echo 'data: '.json_encode(['token' => $chunk])."\n\n";
                ob_flush();
                flush();
            }

            $agent->appendToHistory('user', $message);
            $agent->appendToHistory('assistant', $fullResponse);

            echo "data: [DONE]\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function voiceChat(ChatMessageRequest $request, Student $student): JsonResponse
    {
        $agent = new EnglishTeacherAgent(
            student: $student,
            sessionId: $request->input('session_id'),
            voiceMode: true,
        );

        $response = $agent->prompt($request->input('message'));

        $agent->appendToHistory('user', $request->input('message'));
        $agent->appendToHistory('assistant', (string) $response);

        return response()->json([
            'message' => (string) $response,
            'session_id' => $request->input('session_id'),
            'mode' => 'voice',
        ]);
    }

    public function voiceGreeting(Student $student): JsonResponse
    {
        $agent = new EnglishTeacherAgent(
            student: $student,
            sessionId: 'greeting',
            voiceMode: true,
        );

        return response()->json([
            'greeting' => $agent->getVoiceGreeting(),
        ]);
    }
}
