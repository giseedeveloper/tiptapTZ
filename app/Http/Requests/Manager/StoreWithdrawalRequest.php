<?php

namespace App\Http\Requests\Manager;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
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
        $min = (float) Setting::get('min_withdrawal', 0);

        return [
            'amount' => ['required', 'numeric', 'min:'.$min],
            'payment_method' => ['required', 'string', 'max:100'],
            'payment_details' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $min = number_format((float) Setting::get('min_withdrawal', 0), 0);

        return [
            'amount.min' => 'Minimum withdrawal is '.config('tiptap.currency_symbol').' '.$min.'.',
        ];
    }
}
