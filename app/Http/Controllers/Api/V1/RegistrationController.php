<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use App\Models\User;
use App\Models\YouthProfile;
use App\Services\Safeguarding\AgeCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    public function __construct(private AgeCategoryService $ages)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'organisation_unit_id' => ['nullable', 'exists:organisation_units,id'],
            'school_institution' => ['nullable', 'string', 'max:255'],
            'interests' => ['nullable', 'array'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_relationship' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:40'],
            'guardian_email' => ['nullable', 'email', 'max:190'],
            'guardian_confirmed' => ['nullable', 'boolean'],
        ]);

        try {
            $category = $this->ages->resolve($data['date_of_birth']);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        if ($this->ages->requiresGuardianConsent($category)) {
            validator($data, [
                'guardian_name' => ['required', 'string', 'max:150'],
                'guardian_relationship' => ['required', 'string', 'max:100'],
                'guardian_phone' => ['required', 'string', 'max:40'],
                'guardian_confirmed' => ['required', 'accepted'],
            ])->validate();
        }

        $result = DB::transaction(function () use ($data, $category): array {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            YouthProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $data['date_of_birth'],
                'age_category' => $category,
                'organisation_unit_id' => $data['organisation_unit_id'] ?? null,
                'school_institution' => $data['school_institution'] ?? null,
                'interests' => $data['interests'] ?? [],
                'safeguarding_verified' => false,
            ]);

            if ($category === 'teen') {
                GuardianConsent::create([
                    'user_id' => $user->id,
                    'guardian_name' => $data['guardian_name'],
                    'relationship' => $data['guardian_relationship'],
                    'guardian_phone' => $data['guardian_phone'],
                    'guardian_email' => $data['guardian_email'] ?? null,
                    'status' => 'pending',
                    'consented_at' => now(),
                ]);
            }

            $token = $user->createToken('mobile')->plainTextToken;

            return compact('user', 'token', 'category');
        });

        return response()->json([
            'message' => $category === 'teen'
                ? 'Account created. Guardian consent verification is required before restricted features are enabled.'
                : 'Account created successfully.',
            'data' => $result,
        ], 201);
    }
}
