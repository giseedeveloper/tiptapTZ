<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBotEventRequest;
use App\Services\BotEventService;
use Illuminate\Http\JsonResponse;

class BotEventController extends Controller
{
    public function store(StoreBotEventRequest $request, BotEventService $botEventService): JsonResponse
    {
        $event = $botEventService->recordFromPayload($request->validated());

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'restaurant_id' => $event->restaurant_id,
                'occurred_at' => $event->occurred_at?->toIso8601String(),
            ],
        ], 201);
    }
}
