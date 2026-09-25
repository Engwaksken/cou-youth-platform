<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use App\Models\User;
use App\Models\YouthProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
        return $this->profileResponse($request);
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'school_institution' => ['nullable', 'string', 'max:190'],
            'profile_public' => ['nullable', 'boolean'],
            'profile_photo_base64' => ['nullable', 'string', 'max:7000000'],
            'profile_photo_name' => ['nullable', 'string', 'max:160'],
        ]);

        $user->fill(array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ], static fn ($value) => $value !== null));
        $user->save();

        $profile = YouthProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['profile_public' => false],
        );

        if (array_key_exists('school_institution', $data)) {
            $profile->school_institution = $data['school_institution'];
        }

        if (array_key_exists('profile_public', $data)) {
            $profile->profile_public = (bool) $data['profile_public'];
        }

        if (! empty($data['profile_photo_base64'])) {
            $photoData = $data['profile_photo_base64'];
            if (str_contains($photoData, ',')) {
                [, $photoData] = explode(',', $photoData, 2);
            }

            $binary = base64_decode($photoData, true);
            if ($binary === false || strlen($binary) > 4 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'profile_photo_base64' => ['Please choose an image smaller than 4 MB.'],
                ]);
            }

            $extension = strtolower(pathinfo((string) ($data['profile_photo_name'] ?? ''), PATHINFO_EXTENSION));
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $extension = 'jpg';
            }

            if ($profile->profile_photo) {
                Storage::disk('public')->delete($profile->profile_photo);
            }

            $path = 'profiles/'.$user->id.'/avatar-'.now()->format('YmdHis').'.'.$extension;
            Storage::disk('public')->put($path, $binary);
            $profile->profile_photo = $path;
        }

        $profile->save();

        return $this->profileResponse($request, 'Profile updated successfully.');
    }

    private function profileResponse(Request $request, ?string $message = null)
    {
        $profile = YouthProfile::query()
            ->where('user_id', $request->user()->id)
            ->first();

        $guardianConsent = GuardianConsent::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        $response = [
            'user' => $request->user()->fresh(),
            'youth_profile' => $profile,
            'safeguarding' => [
                'guardian_consent_required' => $profile?->age_category === 'teen',
                'guardian_consent_status' => $guardianConsent?->status,
                'guardian_consent_submitted_at' => $guardianConsent?->consented_at,
                'safeguarding_verified' => (bool) ($profile?->safeguarding_verified ?? false),
            ],
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }
}
