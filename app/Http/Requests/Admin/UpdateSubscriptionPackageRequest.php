<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionPackageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['nullable', 'string', 'max:8'],
            'billing_period' => ['required', 'in:monthly,yearly,trial,one_time'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'table_limit' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'waiter_limit' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['string', 'in:'.implode(',', array_keys(\App\Models\SubscriptionPackage::CAPABILITIES))],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
