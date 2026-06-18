<?php

namespace App\Services;

use App\Enums\BotEngagementEvent;
use App\Enums\BotFunnelStep;
use App\Enums\BotQrEntryType;
use App\Models\BotEvent;
use App\Models\BotSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BotEventService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        BotEngagementEvent $event,
        ?int $restaurantId = null,
        ?string $waId = null,
        ?string $customerPhone = null,
        array $metadata = [],
        ?Carbon $occurredAt = null,
    ): BotEvent {
        return $this->recordRaw(
            eventType: $event->value,
            restaurantId: $restaurantId,
            waId: $waId,
            customerPhone: $customerPhone,
            metadata: $metadata,
            occurredAt: $occurredAt,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function recordQrEntry(
        BotQrEntryType $entryType,
        int $restaurantId,
        ?string $waId = null,
        ?string $customerPhone = null,
        array $metadata = [],
    ): BotEvent {
        return $this->recordRaw(
            eventType: $entryType->value,
            restaurantId: $restaurantId,
            waId: $waId,
            customerPhone: $customerPhone,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function recordFunnelStep(
        BotFunnelStep $step,
        int $restaurantId,
        ?string $waId = null,
        ?string $customerPhone = null,
        array $metadata = [],
    ): BotEvent {
        return $this->recordRaw(
            eventType: $step->value,
            restaurantId: $restaurantId,
            waId: $waId,
            customerPhone: $customerPhone,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $contextData
     */
    public function recordQrEntryFromRequest(Request $request, string $parseType, array $contextData): void
    {
        $entryType = BotQrEntryType::fromParseType($parseType);
        $restaurantId = (int) ($contextData['restaurant_id'] ?? 0);

        if ($entryType === null || $restaurantId <= 0) {
            return;
        }

        $this->recordQrEntry(
            entryType: $entryType,
            restaurantId: $restaurantId,
            waId: $request->input('wa_id'),
            customerPhone: $request->input('customer_phone') ?? $request->input('phone_number'),
            metadata: ['entry' => $parseType],
        );

        $this->recordFunnelStep(
            step: BotFunnelStep::BotHome,
            restaurantId: $restaurantId,
            waId: $request->input('wa_id'),
            customerPhone: $request->input('customer_phone') ?? $request->input('phone_number'),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordFromPayload(array $payload): BotEvent
    {
        return $this->recordRaw(
            eventType: $payload['event_type'],
            restaurantId: isset($payload['restaurant_id']) ? (int) $payload['restaurant_id'] : null,
            waId: $payload['wa_id'] ?? null,
            customerPhone: $payload['customer_phone'] ?? null,
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
            occurredAt: isset($payload['occurred_at']) ? Carbon::parse($payload['occurred_at']) : null,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function recordRaw(
        string $eventType,
        ?int $restaurantId = null,
        ?string $waId = null,
        ?string $customerPhone = null,
        array $metadata = [],
        ?Carbon $occurredAt = null,
    ): BotEvent {
        $normalizedWaId = $this->normalizeWaId($waId, $customerPhone);

        return BotEvent::query()->create([
            'wa_id' => $normalizedWaId,
            'restaurant_id' => $restaurantId,
            'event_type' => $eventType,
            'metadata' => $metadata === [] ? null : $metadata,
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }

    private function normalizeWaId(?string $waId, ?string $customerPhone): ?string
    {
        if (is_string($waId) && trim($waId) !== '') {
            return BotSession::normalizeWaId($waId);
        }

        if (is_string($customerPhone) && trim($customerPhone) !== '') {
            return BotSession::normalizeWaId($customerPhone);
        }

        return null;
    }
}
