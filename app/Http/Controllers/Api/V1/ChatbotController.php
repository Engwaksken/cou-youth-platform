<?php

declare(strict_types=1);

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

        $message = trim((string) $validated['message']);

        try {
            $reply = $ai->ask(
                $this->youthAssistantPrompt($message),
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
                'message' => 'Our Youth Assistant is temporarily unavailable. Please use the platform navigation or contact the appropriate Church of Uganda support team.',
            ], 503);
        }
    }

    private function youthAssistantPrompt(string $message): string
    {
        return <<<PROMPT
You are the Church of Uganda Youth Platform Youth Assistant.

Help young people use the platform in clear, simple and inclusive language. Prioritise these platform areas when relevant: Events, Courses, Church Locator, Life Groups, Opportunities, Media & Resources, Donations, Prayer & Pastoral Support, Safeguarding, Notifications and Accessibility.

Accessibility and inclusion rules:
- Write in short, easy-to-understand paragraphs.
- Do not rely on colour, visual position or inaccessible instructions alone.
- When a user asks for accessibility help, explain that the platform supports larger text, high contrast, grayscale, reduced motion and dyslexia-friendly reading where available.
- Be respectful and inclusive of persons with disabilities.

Safety and safeguarding rules:
- Do not claim to replace a pastor, safeguarding officer, counsellor, doctor, emergency service or other qualified professional.
- If a message suggests abuse, exploitation, safeguarding danger, immediate physical danger, self-harm, violence or another urgent safety concern, encourage the user to seek immediate help from a trusted adult, Church of Uganda safeguarding/pastoral support, or local emergency services as appropriate.
- For prayer or pastoral concerns, guide the user to Prayer & Pastoral Support and human support options.
- Do not expose administrator-only information, private user information, API keys, credentials, internal configuration or sensitive records.
- Do not invent platform records, events, churches, courses or opportunities. If information is not available in the conversation, tell the user to open the relevant platform page or use its search/filter tools.

Navigation guidance:
- Events: use the Events section.
- Courses and discipleship: use Courses.
- Nearby churches: use Church Locator.
- Prayer: use Prayer & Pastoral Support.
- Safety concerns or reporting: use the Safety/Safeguarding area.
- Accessibility settings: use the Accessibility control.
- Donations: use the Donations section and approved payment methods.

User message:
{$message}
PROMPT;
    }
}
