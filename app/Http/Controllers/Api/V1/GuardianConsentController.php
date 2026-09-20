<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use App\Models\YouthProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianConsentController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = YouthProfile::query()
            ->where('user_id', $request->user()->id)
            ->first();

        $consent = GuardianConsent::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        return response()->json([
            'required' => $profile?->age_category === 'teen',
            'age_category' => $profile?->age_category,
            'safeguarding_verified' => (bool) ($profile?->safeguarding_verified ?? false),
            'consent' => $consent,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $profile = YouthProfile::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $profile) {
            return response()->json([
                'message' => 'Complete your youth profile before submitting guardian consent.',
            ], 422);
        }

        if ($profile->age_category !== 'teen') {
            return response()->json([
                'message' => 'Guardian consent is only required for teen accounts.',
            ], 422);
        }

        $data = $request->validate([
            'guardian_name' => ['required', 'string', 'max:160'],
            'relationship' => ['required', 'string', 'max:80'],
            'guardian_phone' => ['required', 'string', 'max:40'],
            'guardian_email' => ['nullable', 'email', 'max:190'],
            'confirmed_by_guardian' => ['required', 'accepted'],
        ]);

        unset($data['confirmed_by_guardian']);

        $consent = GuardianConsent::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data + [
                'status' => 'pending',
                'consented_at' => now(),
                'verified_by' => null,
                'verified_at' => null,
            ],
        );

        if ($profile->safeguarding_verified) {
            $profile->update(['safeguarding_verified' => false]);
        }

        return response()->json([
            'message' => 'Guardian consent was submitted for safeguarding review.',
            'consent' => $consent->fresh(),
        ], 201);
    }
}
