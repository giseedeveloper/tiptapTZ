<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminPortalAccess;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBotBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return AdminPortalAccess::can($this->user(), 'admin.technical.bots');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bot_id' => ['required', 'integer', 'exists:bots,id'],
            'welcome_title' => ['required', 'string', 'max:120'],
            'welcome_body' => ['nullable', 'string', 'max:500'],
            'welcome_image' => ['nullable', 'image', 'max:2048'],
            'remove_welcome_image' => ['sometimes', 'boolean'],
        ];
    }
}
