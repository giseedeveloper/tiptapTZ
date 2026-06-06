<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayoutProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('manager') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payout_method' => ['required', 'string', Rule::in(['Mobile Money', 'Bank Transfer'])],
            'payout_details' => ['required', 'string', 'max:500'],
        ];
    }
}
