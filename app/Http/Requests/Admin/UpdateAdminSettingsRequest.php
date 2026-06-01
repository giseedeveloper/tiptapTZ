<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'system_name' => ['required', 'string', 'max:255'],
            'support_email' => ['required', 'email', 'max:255'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_withdrawal' => ['required', 'numeric', 'min:0'],
            'demo_push' => ['required', Rule::in(['0', '1', 0, 1])],
            'whatsapp_bot_number' => ['required', 'string', 'max:30', 'regex:/^\d+$/'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
        ];
    }
}
