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
        $useSaved = $this->boolean('use_saved_payout');

        return [
            'amount' => ['required', 'numeric', 'min:'.$min],
            'use_saved_payout' => ['nullable', 'boolean'],
            'payment_method' => [$useSaved ? 'nullable' : 'required', 'string', 'max:100'],
            'payment_details' => [$useSaved ? 'nullable' : 'required', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->boolean('use_saved_payout')) {
                return;
            }

            $restaurant = $this->user()?->restaurant;

            if (! $restaurant?->hasPayoutProfile()) {
                $validator->errors()->add(
                    'use_saved_payout',
                    'Save your payout profile first, or enter payout details manually.'
                );
            }
        });
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

    /**
     * @return array{payment_method: string, payment_details: string}
     */
    public function payoutDetails(): array
    {
        if ($this->boolean('use_saved_payout')) {
            $restaurant = $this->user()->restaurant;

            return [
                'payment_method' => (string) $restaurant->payout_method,
                'payment_details' => (string) $restaurant->payout_details,
            ];
        }

        return [
            'payment_method' => (string) $this->validated('payment_method'),
            'payment_details' => (string) $this->validated('payment_details'),
        ];
    }
}
