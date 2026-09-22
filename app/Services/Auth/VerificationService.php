<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\Branding\BrandingService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class VerificationService
{
    public function __construct(private readonly BrandingService $branding) {}

    public function issue(User $user, string $purpose, int $minutes = 10): void
    {
        VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->delete();

        $code = (string) random_int(100000, 999999);

        VerificationCode::create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($minutes),
        ]);

        [$subjectLine, $heading, $intro] = $this->copyFor($purpose);
        $brand = $this->branding->data();

        Mail::send('emails.verification-code', compact(
            'user',
            'code',
            'minutes',
            'brand',
            'subjectLine',
            'heading',
            'intro',
        ), function ($message) use ($user, $subjectLine, $brand): void {
            $message->to($user->email, $user->name)
                ->subject($subjectLine)
                ->from(
                    (string) config('mail.from.address'),
                    $brand['short_name'] ?: (string) config('mail.from.name'),
                );
        });
    }

    public function verify(User $user, string $purpose, string $code): void
    {
        $row = VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $row || $row->expires_at->isPast() || ! Hash::check($code, $row->code_hash)) {
            if ($row) {
                $row->increment('attempts');
            }

            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or expired.'],
            ]);
        }

        $row->update(['used_at' => now()]);
    }

    private function copyFor(string $purpose): array
    {
        return match ($purpose) {
            'password_reset' => [
                'Reset your COU Youth Platform password',
                'Reset your password',
                'Use the verification code below to choose a new password for your youth account.',
            ],
            'login_otp' => [
                'Your COU Youth Platform sign-in code',
                'Your sign-in code',
                'Use the verification code below to securely sign in to your youth account.',
            ],
            'email_verification' => [
                'Verify your COU Youth Platform email',
                'Verify your email address',
                'Use the verification code below to confirm your email address.',
            ],
            default => [
                'Your COU Youth Platform verification code',
                'Verification code',
                'Use the verification code below to continue securely.',
            ],
        };
    }
}
