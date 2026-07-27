<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ForwardWhatsAppWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp Cloud API webhook entry point (forwarder mode to Node bot).
 */
class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $mode = (string) $request->query('hub_mode', '');
        $token = (string) $request->query('hub_verify_token', '');
        $challenge = (string) $request->query('hub_challenge', '');

        $expected = (string) config('services.whatsapp.verify_token', '');

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, $token)) {
            return response($challenge, 200);
        }

        Log::warning('WhatsApp webhook verification failed.', [
            'mode' => $mode,
            'token_match' => $expected !== '' && hash_equals($expected, $token),
        ]);

        return response('Forbidden', 403);
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $rawBody = $request->getContent();

        if (! $this->signatureIsValid($request, $rawBody)) {
            Log::warning('WhatsApp webhook signature invalid; rejecting.');

            return response()->json(['status' => 'invalid_signature'], 401);
        }

        ForwardWhatsAppWebhook::dispatch($payload);

        return response()->json(['status' => 'received']);
    }

    protected function signatureIsValid(Request $request, string $rawBody): bool
    {
        $appSecret = (string) config('services.whatsapp.app_secret', '');

        if ($appSecret === '') {
            return app()->environment(['local', 'testing']);
        }

        $header = $request->header('X-Hub-Signature-256', '');

        if (! is_string($header) || $header === '' || ! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $rawBody, $appSecret);

        return hash_equals($expected, $header);
    }
}
