<?php

namespace App\Jobs;

use App\Support\WhatsAppWebhookForwarder;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForwardWhatsAppWebhook implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $uniqueFor = 300;

    public function __construct(public array $payload) {}

    public function uniqueId(): string
    {
        return hash('sha256', json_encode($this->payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 30, 60, 120, 300];
    }

    public function handle(): void
    {
        $target = WhatsAppWebhookForwarder::resolve($this->payload);

        if ($target === null) {
            Log::info('WhatsApp webhook payload received (no bot forward URL set).', [
                'payload_keys' => array_keys($this->payload),
            ]);

            return;
        }

        Log::info('Forwarding WhatsApp webhook to bot.', [
            'phone_number_id' => WhatsAppWebhookForwarder::extractPhoneNumberId($this->payload),
            'url' => $target['url'],
        ]);

        Http::withHeaders(['X-Bot-Secret' => $target['secret']])
            ->timeout(min((int) config('whatsapp.bot_notify_timeout', 10), 15))
            ->acceptJson()
            ->asJson()
            ->post($target['url'], $this->payload)
            ->throw();
    }
}
