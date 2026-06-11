<?php

namespace App\Services;

use App\Models\Order;

class BotFeedbackService
{
    public function resolveLatestOrderId(int $restaurantId, ?string $customerPhone, ?int $explicitOrderId = null): ?int
    {
        if ($explicitOrderId) {
            $order = Order::withoutGlobalScopes()
                ->where('id', $explicitOrderId)
                ->where('restaurant_id', $restaurantId)
                ->first();

            return $order?->id;
        }

        if ($customerPhone === null || trim($customerPhone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $customerPhone);
        if ($digits === '') {
            return null;
        }

        $order = Order::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->where(function ($query) use ($digits) {
                $query->where('customer_phone', 'like', '%'.$digits)
                    ->orWhere('whatsapp_jid', 'like', '%'.$digits.'%');
            })
            ->latest('id')
            ->first();

        return $order?->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(array $validated): array
    {
        $type = \App\Enums\FeedbackType::from($validated['type']);

        $payload = [
            'restaurant_id' => $validated['restaurant_id'],
            'type' => $type->value,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ];

        if ($type === \App\Enums\FeedbackType::Food) {
            $orderId = $this->resolveLatestOrderId(
                (int) $validated['restaurant_id'],
                $validated['customer_phone'] ?? null,
                isset($validated['order_id']) ? (int) $validated['order_id'] : null,
            );

            if ($orderId === null) {
                throw new \InvalidArgumentException('no_order_for_food_rating');
            }

            $payload['order_id'] = $orderId;
            $payload['waiter_id'] = null;

            return $payload;
        }

        if ($type === \App\Enums\FeedbackType::Waiter) {
            $waiterId = $validated['waiter_id'] ?? null;

            if (empty($waiterId) && ! empty($validated['order_id'])) {
                $order = Order::withoutGlobalScopes()->find($validated['order_id']);
                $waiterId = $order?->waiter_id;
            }

            $payload['waiter_id'] = $waiterId;
            $payload['order_id'] = $validated['order_id'] ?? null;

            return $payload;
        }

        $payload['waiter_id'] = null;
        $payload['order_id'] = $validated['order_id'] ?? null;

        return $payload;
    }
}
