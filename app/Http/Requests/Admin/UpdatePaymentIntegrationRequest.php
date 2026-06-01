<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentIntegrationRequest extends FormRequest
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
            'payment_vendor_id' => ['required', 'string', 'max:255'],
            'payment_api_key' => ['required', 'string', 'max:255'],
            'payment_api_secret' => ['required', 'string', 'max:255'],
            'payment_is_live' => ['nullable', 'boolean'],
        ];
    }
}
