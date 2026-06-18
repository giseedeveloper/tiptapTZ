<?php

namespace App\Http\Requests;

use App\Enums\BotAnalyticsEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBotEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', Rule::in(BotAnalyticsEvent::values())],
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'wa_id' => ['nullable', 'string', 'max:32'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'metadata' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
