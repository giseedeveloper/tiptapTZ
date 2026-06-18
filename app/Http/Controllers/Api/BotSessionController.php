<?php

namespace App\Http\Controllers\Api;

use App\Enums\BotEngagementEvent;
use App\Http\Controllers\Controller;
use App\Models\BotSession;
use App\Services\BotEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Persistent storage for WhatsApp bot session state.
 *
 * Replaces the in-memory `sessions[from]` map that previously lived inside the
 * Node bot. The bot now reads/writes the session over this API so state
 * survives bot restarts and can be inspected centrally.
 *
 * Auth: same Sanctum bearer token (`bot_service` role) used by /api/bot/*.
 */
class BotSessionController extends Controller
{
    /**
     * Fetch the current session for a WhatsApp id.
     *
     * Returns 200 with a fresh default payload (no state) when the row does
     * not yet exist, so callers can always rely on a session object.
     */
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

    /**
     * Upsert the session for a WhatsApp id.
     *
     * Body shape:
     *   {
     *     "wa_id": "255712345678",
     *     "state": "HOME",
     *     "lang": "en",
     *     "data": { ... arbitrary cart / restaurant / table fields ... }
     *   }
     */
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wa_id' => 'required|string|max:32',
            'state' => 'nullable|string|max:64',
            'lang' => 'nullable|string|max:2',
            'data' => 'nullable|array',
        ]);

        $waId = BotSession::normalizeWaId($validated['wa_id']);

        $existing = BotSession::query()->where('wa_id', $waId)->first();
        $previousLang = $existing?->lang;
        $newLang = $validated['lang'] ?? 'en';

        $session = BotSession::query()->updateOrCreate(
            ['wa_id' => $waId],
            [
                'state' => $validated['state'] ?? 'START',
                'lang' => $newLang,
                'data' => $validated['data'] ?? [],
                'last_message_at' => now(),
            ]
        );

        if ($previousLang !== null && $previousLang !== $newLang) {
            $restaurantId = (int) (($validated['data'] ?? $session->data ?? [])['restaurant_id'] ?? 0);

            if ($restaurantId > 0) {
                app(BotEventService::class)->record(
                    event: BotEngagementEvent::ChangeLanguage,
                    restaurantId: $restaurantId,
                    waId: $waId,
                    metadata: ['lang' => $newLang, 'previous_lang' => $previousLang],
                );
            }
        }

        return response()->json([
            'success' => true,
            'data' => $this->serialize($session->fresh()),
        ]);
    }

    /**
     * Clear a session (e.g. customer types "exit" or the bot completes a flow).
     */
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
