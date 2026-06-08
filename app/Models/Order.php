<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['restaurant_id', 'waiter_id', 'table_number', 'customer_phone', 'customer_name', 'whatsapp_jid', 'status', 'payment_reference', 'total_amount', 'notes', 'is_vip', 'bill_image_pushed_at'];

    protected function casts(): array
    {
        return [
            'bill_image_pushed_at' => 'datetime',
            'is_vip' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\RestaurantScope);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    public function tip()
    {
        return $this->hasOne(Tip::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function isBillStage(): bool
    {
        return in_array($this->status, ['served'], true);
    }

    public function billImageSignature(): string
    {
        $seed = implode('|', [
            $this->id,
            $this->restaurant_id,
            (string) $this->updated_at,
        ]);

        return hash_hmac('sha256', $seed, (string) config('app.key'));
    }

    public function billImageUrl(): string
    {
        $signature = $this->billImageSignature();
        $base = rtrim((string) config('whatsapp.bill_image_base_url'), '/');
        if ($base !== '') {
            return $base.'/bill-image/'.$this->id.'/'.$signature;
        }

        return route('bill.image', [
            'orderId' => $this->id,
            'signature' => $signature,
        ]);
    }

    public function shouldPushBillImage(): bool
    {
        return $this->isBillStage()
            && ! empty($this->whatsapp_jid)
            && is_null($this->bill_image_pushed_at);
    }

    public function markBillImagePushed(): void
    {
        $this->forceFill(['bill_image_pushed_at' => now()])->saveQuietly();
    }

    /**
     * Normalize JID for storage / bill push. Preserves full addresses from the bot
     * (e.g. LID suffix) when provided; otherwise builds digits plus @s.whatsapp.net from customer_phone.
     */
    /**
     * Digits-only WhatsApp id for Cloud API outbound (from stored jid or phone).
     */
    public static function whatsAppRecipientId(?string $providedJid, ?string $customerPhone): ?string
    {
        $normalized = self::normalizeWhatsAppJid($providedJid, $customerPhone);
        if ($normalized === null || $normalized === '') {
            return null;
        }

        $local = explode('@', $normalized)[0];
        $digits = preg_replace('/\D+/', '', $local);

        return $digits !== '' ? $digits : null;
    }

    public static function normalizeWhatsAppJid(?string $providedJid, ?string $customerPhone): ?string
    {
        $provided = $providedJid !== null ? trim($providedJid) : '';
        if ($provided !== '') {
            if (str_contains($provided, '@')) {
                return $provided;
            }

            if (ctype_digit($provided)) {
                return self::whatsappJidFromDigits($provided);
            }
        }

        $digitsOnlyPhone = preg_replace('/\D+/', '', (string) $customerPhone);
        if ($digitsOnlyPhone === '') {
            return null;
        }

        return self::whatsappJidFromDigits($digitsOnlyPhone);
    }

    /**
     * Build {msisdn}@s.whatsapp.net from digits. Maps local 0 + 9 digits to country code.
     */
    protected static function whatsappJidFromDigits(string $digits): string
    {
        $countryCode = (string) config('tiptap.country_code', '255');

        if (preg_match('/^0(\d{9})$/', $digits, $matches) === 1) {
            return $countryCode.$matches[1].'@s.whatsapp.net';
        }

        if (str_starts_with($digits, $countryCode) && strlen($digits) >= 11) {
            return $digits.'@s.whatsapp.net';
        }

        return $digits.'@s.whatsapp.net';
    }
}
