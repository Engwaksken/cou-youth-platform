<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'in:android,ios,web'],
            'push_token' => ['required', 'string', 'max:4096'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
