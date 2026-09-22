<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use App\Models\User;
use App\Models\YouthProfile;
use App\Services\Auth\VerificationService;
use App\Services\Safeguarding\AgeCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

final class YouthAuthController extends Controller
{
    public function __construct(private readonly AgeCategoryService $ages) {}

    public function showLogin(): View
    {
        return view('auth.youth-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister(): View
    {
        return view('auth.youth-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'school_institution' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_relationship' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:40'],
            'guardian_email' => ['nullable', 'email', 'max:190'],
            'guardian_confirmed' => ['nullable', 'boolean'],
        ]);

        try {
            $category = $this->ages->resolve($data['date_of_birth']);
        } catch (\InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['date_of_birth' => $exception->getMessage()]);
        }

        if ($this->ages->requiresGuardianConsent($category)) {
            validator($data, [
                'guardian_name' => ['required', 'string', 'max:150'],
                'guardian_relationship' => ['required', 'string', 'max:100'],
                'guardian_phone' => ['required', 'string', 'max:40'],
                'guardian_confirmed' => ['required', 'accepted'],
            ])->validate();
        }

        $user = DB::transaction(function () use ($data, $category): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            YouthProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $data['date_of_birth'],
                'age_category' => $category,
                'school_institution' => $data['school_institution'] ?? null,
                'interests' => [],
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

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Your youth account has been created successfully.');
    }

    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function requestReset(Request $request, VerificationService $verification): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::query()->where('email', $data['email'])->first();

        if ($user) {
            $verification->issue($user, 'password_reset');
        }

        return redirect()->route('password.reset.form', ['email' => $data['email']])
            ->with('success', 'If the account exists, a six-digit reset code has been sent.');
    }

    public function showReset(Request $request): View
    {
        return view('auth.reset-password', ['email' => (string) $request->query('email', '')]);
    }

    public function resetPassword(Request $request, VerificationService $verification): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::query()->where('email', $data['email'])->first();
        if (! $user) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'The reset request could not be verified.']);
        }

        $verification->verify($user, 'password_reset', $data['code']);
        $user->update(['password' => Hash::make($data['password'])]);
        $user->tokens()->delete();

        return redirect()->route('login')->with('success', 'Password updated successfully. You can now sign in.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
