<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Support\ChurchOfUgandaDioceses;
use App\Support\UserProfilePhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** The signed-in admin's own profile: details, photo and password. */
final class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['diocese', 'youthProfile.diocese', 'organisationRoles.organisationUnit']);

        return view('admin.users.show', [
            'user' => $user,
            'dioceses' => ChurchOfUgandaDioceses::options(),
            'isSelf' => true,
            'formAction' => route('admin.profile.update'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($request->input('password') === null || $request->input('password') === '') {
            $request->request->remove('password');
            $request->request->remove('password_confirmation');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40', 'regex:'.StoreUserRequest::PHONE_PATTERN],
            'location' => ['nullable', 'string', 'max:255'],
            'diocese_id' => ['nullable', 'integer', ChurchOfUgandaDioceses::existsRule()],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
            'password' => ['sometimes', 'confirmed', Password::defaults()],
            ...UserProfilePhoto::rules(),
        ], ['phone.regex' => 'Enter a valid phone number, e.g. +256 772 123456.'], ['diocese_id' => 'diocese']);
        unset($data['current_password']);

        UserProfilePhoto::updateUser($user, $data);

        return back()->with('success', 'Profile updated.');
    }
}
