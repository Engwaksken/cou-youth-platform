<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\ChurchOfUgandaDioceses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('user')) ?? false; }

    protected function prepareForValidation(): void
    {
        if ($this->has('password') && ($this->input('password') === null || $this->input('password') === '')) {
            $this->getInputSource()->remove('password');
            $this->getInputSource()->remove('password_confirmation');
        }
    }

    public function rules(): array
    {
        $user = $this->route('user');
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.($user?->id ?? '')],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40', 'regex:'.StoreUserRequest::PHONE_PATTERN],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'diocese_id' => ['sometimes', 'nullable', 'integer', ChurchOfUgandaDioceses::existsRule()],
            'password' => ['sometimes', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['diocese_id' => 'diocese'];
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Enter a valid phone number, e.g. +256 772 123456.'];
    }
}
