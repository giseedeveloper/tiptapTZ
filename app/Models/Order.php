<?php

namespace App\Models;

use App\Support\OrderWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'restaurant_id',
        'waiter_id',
        'table_number',
        'customer_phone',
        'customer_name',
        'whatsapp_jid',
        'status',
        'payment_reference',
        'total_amount',
        'notes',
        'is_vip',
        'bill_image_pushed_at',
        'received_at',
        'accepted_at',
        'preparing_at',
        'ready_at',
        'served_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'bill_image_pushed_at' => 'datetime',
            'is_vip' => 'boolean',
            'received_at' => 'datetime',
            'accepted_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'served_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\RestaurantScope);

        static::creating(function (Order $order) {
            $order->status = OrderWorkflow::normalize($order->status ?: OrderWorkflow::RECEIVED);
            if (empty($order->received_at) && $order->status !== OrderWorkflow::CANCELLED) {
                $order->received_at = now();
            }
        });

        static::updating(function (Order $order) {
            if ($order->isDirty('status')) {
                $order->status = OrderWorkflow::normalize($order->status);
            }
        });
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(OrderStatusEvent::class);
    }

    public function workflowStatus(): string
    {
        return OrderWorkflow::normalize($this->status);
    }

    public function workflowLabel(): string
    {
        return OrderWorkflow::label($this->status);
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

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
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
        return OrderWorkflow::isBillStage($this->status);
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
     * Build {msisdn}@s.whatsapp.net from digits. Maps SA local 0 + 9 digits to 27…
     */
    protected static function whatsappJidFromDigits(string $digits): string
    {
        $countryCode = (string) config('tiptap.country_code', '27');

        if (preg_match('/^0(\d{9})$/', $digits, $matches) === 1) {
            return $countryCode.$matches[1].'@s.whatsapp.net';
        }

        if (str_starts_with($digits, $countryCode) && strlen($digits) >= 11) {
            return $digits.'@s.whatsapp.net';
        }

        return $digits.'@s.whatsapp.net';
    }
}
