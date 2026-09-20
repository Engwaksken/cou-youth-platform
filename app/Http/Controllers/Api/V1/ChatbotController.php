<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AI\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatbotController extends Controller
{
    public function reply(Request $request, AiService $ai): JsonResponse
    {
        return $this->handle($request, $ai);
    }

    public function __invoke(Request $request, AiService $ai): JsonResponse
    {
        return $this->handle($request, $ai);
    }

    private function handle(Request $request, AiService $ai): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        try {
            $reply = $ai->ask(
                $validated['message'],
                'chatbot',
                $request->user()?->id,
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'reply' => $reply,
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Our assistant is temporarily unavailable. Please try again later.',
            ], 503);
        }
    }
}
