<?php

namespace App\Console\Commands;

use App\Support\WhatsAppBotUrls;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WhatsAppSetupDoctor extends Command
{
    protected $signature = 'whatsapp:doctor {--probe : HTTP GET bot /health when notify URL is set}';

    protected $description = 'Check WhatsApp Cloud API + tiptopbot configuration for TAPTAP TZ';

    public function handle(): int
    {
        $this->info('TAPTAP TZ — WhatsApp Cloud API setup check');
        $this->newLine();

        $ok = true;

        $ok = $this->checkVar('WHATSAPP_PHONE_NUMBER_ID', config('services.whatsapp.phone_number_id')) && $ok;
        $ok = $this->checkVar('WHATSAPP_ACCESS_TOKEN', config('services.whatsapp.access_token')) && $ok;
        $ok = $this->checkVar('WHATSAPP_VERIFY_TOKEN', config('services.whatsapp.verify_token')) && $ok;
        $ok = $this->checkVar('WHATSAPP_APP_SECRET', config('services.whatsapp.app_secret'), required: true) && $ok;
        $ok = $this->checkVar('WHATSAPP_BOT_NOTIFY_URL', config('whatsapp.bot_notify_url')) && $ok;
        $ok = $this->checkVar('WHATSAPP_BOT_NOTIFY_SECRET', config('whatsapp.bot_notify_secret')) && $ok;

        $this->newLine();
        $this->line('Meta webhook (forwarder mode — recommended):');
        $this->line('  Callback URL: '.WhatsAppBotUrls::laravelWebhookUrl());
        $this->line('  Verify token: '.(config('services.whatsapp.verify_token') ?: '(set WHATSAPP_VERIFY_TOKEN)'));

        $inbound = WhatsAppBotUrls::inboundForwardUrl();
        if ($inbound !== null) {
            $this->newLine();
            $this->line('Laravel forwards signed webhooks to:');
            $this->line('  '.$inbound);
            $this->line('  Header: X-Bot-Secret = WHATSAPP_BOT_NOTIFY_SECRET (same as tiptopbot NOTIFY_SECRET)');
        }

        $this->newLine();
        $this->line('Bot VPS: wa-notify.tiptapafrica.co.tz | Laravel: tiptapafrica.co.tz');
        $this->line('tiptopbot/.env must mirror Meta vars + NOTIFY_SECRET + BOT_TOKEN + API_BASE_URL.');

        if ($this->option('probe') && WhatsAppBotUrls::botBaseUrl() !== null) {
            $this->newLine();
            $healthUrl = WhatsAppBotUrls::botBaseUrl().'/health';
            $this->line('Probing '.$healthUrl.' …');
            try {
                $response = Http::timeout(8)->get($healthUrl);
                if ($response->successful() && ($response->json('ok') === true || $response->json('service') === 'tiptopbot')) {
                    $this->info('Bot health OK.');
                } else {
                    $this->warn('Unexpected health response: HTTP '.$response->status());
                    $ok = false;
                }
            } catch (\Throwable $e) {
                $this->error('Could not reach bot: '.$e->getMessage());
                $ok = false;
            }
        }

        $this->newLine();

        if (! $ok) {
            $this->error('Fix the items marked MISSING above, then run: php artisan whatsapp:doctor --probe');

            return self::FAILURE;
        }

        $this->info('Configuration looks complete. Verify webhook in Meta Console, then send a test WhatsApp message.');

        return self::SUCCESS;
    }

    protected function checkVar(string $label, mixed $value, bool $required = true): bool
    {
        $empty = $value === null || $value === '';

        if ($empty && $required) {
            $this->error("  MISSING  {$label}");

            return false;
        }

        if ($empty) {
            $this->warn("  optional empty  {$label}");

            return true;
        }

        $this->info("  OK       {$label}");

        return true;
    }
}
