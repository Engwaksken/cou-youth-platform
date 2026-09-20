<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use App\Models\User;
use App\Models\YouthProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! method_exists($user, 'createToken')) {
            return response()->json([
                'message' => 'Laravel Sanctum is required for mobile authentication.',
            ], 500);
        }

        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Signed out successfully.',
        ]);
    }

    public function me(Request $request)
    {
        $profile = YouthProfile::query()
            ->where('user_id', $request->user()->id)
            ->first();

        $guardianConsent = GuardianConsent::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        return response()->json([
            'user' => $request->user(),
            'youth_profile' => $profile,
            'safeguarding' => [
                'guardian_consent_required' => $profile?->age_category === 'teen',
                'guardian_consent_status' => $guardianConsent?->status,
                'guardian_consent_submitted_at' => $guardianConsent?->consented_at,
                'safeguarding_verified' => (bool) ($profile?->safeguarding_verified ?? false),
            ],
        ]);
    }
}
