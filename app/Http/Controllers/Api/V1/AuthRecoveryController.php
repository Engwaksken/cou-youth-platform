<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthRecoveryController extends Controller
{
    public function requestEmailVerification(Request $request, VerificationService $verification)
    {
        $verification->issue($request->user(), 'email_verification');

        return response()->json(['message' => 'Verification code sent.']);
    }

    public function verifyEmail(Request $request, VerificationService $verification)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();
        $verification->verify($user, 'email_verification', $data['code']);

        if (method_exists($user, 'markEmailAsVerified')) {
            $user->markEmailAsVerified();
        }

        return response()->json(['message' => 'Email verified successfully.']);
    }

    public function requestPasswordReset(Request $request, VerificationService $verification)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::query()->where('email', $data['email'])->first();

        if ($user) {
            $verification->issue($user, 'password_reset');
        }

        return response()->json(['message' => 'If the account exists, a reset code has been sent.']);
    }

    public function resetPassword(Request $request, VerificationService $verification)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::query()->where('email', $data['email'])->firstOrFail();
        $verification->verify($user, 'password_reset', $data['code']);
        $user->update(['password' => Hash::make($data['password'])]);
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function requestLoginOtp(Request $request, VerificationService $verification)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::query()->where('email', $data['email'])->first();

        if ($user) {
            $verification->issue($user, 'login_otp');
        }

        return response()->json(['message' => 'If the account exists, a login code has been sent.']);
    }

    public function verifyLoginOtp(Request $request, VerificationService $verification)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::query()->where('email', $data['email'])->firstOrFail();
        $verification->verify($user, 'login_otp', $data['code']);
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
}
