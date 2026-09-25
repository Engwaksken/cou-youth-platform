<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Relies on route ['auth', 'cms.access'] middleware; no Gate/policy exists.
        return (bool) $this->user();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page' => 'required|string|max:50',
            'title' => 'nullable|string|max:180',
            'body' => 'nullable|string|max:2000',
            'icon' => ['nullable', 'string', 'max:80', 'regex:/^(fa-[a-z0-9\- ]+|[a-z0-9\- ]+)$/'],
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'link_url' => ['nullable', 'string', 'max:255', 'regex:/^(\/|https?:\/\/[^\s]+)$/'],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
