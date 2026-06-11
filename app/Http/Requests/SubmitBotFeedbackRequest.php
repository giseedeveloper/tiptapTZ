<?php

namespace App\Http\Requests;

use App\Enums\FeedbackType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitBotFeedbackRequest extends FormRequest
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
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'type' => ['required', 'string', Rule::in(array_column(FeedbackType::cases(), 'value'))],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'waiter_id' => ['nullable', 'integer', 'exists:users,id'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
