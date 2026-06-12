<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Persistent storage for WhatsApp bot session state.
 */
class BotSessionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $waId = $this->resolveWaId($request);

        if ($waId === null) {
            return response()->json([
                'success' => false,
                'message' => 'wa_id is required',
            ], 422);
        }

        $session = BotSession::query()->where('wa_id', $waId)->first();

        if (! $session) {
            return response()->json([
                'success' => true,
                'exists' => false,
                'data' => $this->defaultPayload($waId),
            ]);
        }

        if ($session->isIdleExpired()) {
            $restaurantName = $session->restaurantNameFromData();
            $lang = $session->lang ?? 'en';
            $session->delete();

            return response()->json([
                'success' => true,
                'exists' => false,
                'expired' => true,
                'expired_restaurant_name' => $restaurantName,
                'lang' => $lang,
                'idle_hours' => BotSession::idleTimeoutHours(),
                'data' => $this->defaultPayload($waId),
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => true,
            'data' => $this->serialize($session),
        ]);
    }

    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wa_id' => 'required|string|max:32',
            'state' => 'nullable|string|max:64',
            'lang' => 'nullable|string|max:2',
            'data' => 'nullable|array',
        ]);

        $waId = BotSession::normalizeWaId($validated['wa_id']);

        $session = BotSession::query()->updateOrCreate(
            ['wa_id' => $waId],
            [
                'state' => $validated['state'] ?? 'START',
                'lang' => $validated['lang'] ?? 'en',
                'data' => $validated['data'] ?? [],
                'last_message_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $this->serialize($session->fresh()),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $waId = $this->resolveWaId($request);

        if ($waId === null) {
            return response()->json([
                'success' => false,
                'message' => 'wa_id is required',
            ], 422);
        }

        BotSession::query()->where('wa_id', $waId)->delete();

        return response()->json(['success' => true]);
    }

    protected function resolveWaId(Request $request): ?string
    {
        $raw = (string) ($request->input('wa_id') ?? $request->query('wa_id') ?? '');

        if ($raw === '') {
            return null;
        }

        return BotSession::normalizeWaId($raw);
    }

    /**
     * @return array{wa_id:string,state:string,lang:string,data:array<string,mixed>,last_message_at:?string}
     */
    protected function serialize(BotSession $session): array
    {
        return [
            'wa_id' => $session->wa_id,
            'state' => $session->state,
            'lang' => $session->lang,
            'data' => $session->data ?? [],
            'last_message_at' => optional($session->last_message_at)->toIso8601String(),
        ];
    }

    /**
     * @return array{wa_id:string,state:string,lang:string,data:array<string,mixed>,last_message_at:null}
     */
    protected function defaultPayload(string $waId): array
    {
        return [
            'wa_id' => $waId,
            'state' => 'START',
            'lang' => 'en',
            'data' => [],
            'last_message_at' => null,
        ];
    }
}
