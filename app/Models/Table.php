<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = ['restaurant_id', 'waiter_id', 'name', 'qr_code', 'capacity', 'is_active', 'table_tag'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\RestaurantScope);

        // Auto-generate table_tag on create
        static::creating(function ($table) {
            if (empty($table->table_tag) && $table->restaurant_id) {
                $restaurant = Restaurant::find($table->restaurant_id);
                if ($restaurant && $restaurant->tag_prefix) {
                    $table->table_tag = $restaurant->generateTableTag();
                }
            }
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    /**
     * Get WhatsApp QR URL for this table
     */
    public function getWhatsappQrUrlAttribute()
    {
        $botNumber = \App\Models\Setting::get('whatsapp_bot_number', config('tiptap.default_whatsapp_bot_number'));
        // Strip non-numeric characters
        $cleanNumber = preg_replace('/[^0-9]/', '', $botNumber);

        // Format: START_{restaurant_id}_T{table_id}
        $message = 'START_'.$this->restaurant_id.'_T'.$this->id;

        return 'https://wa.me/'.$cleanNumber.'?text='.urlencode($message);
    }

    /**
     * Get the tag-based entry URL (using table_tag)
     */
    public function getTagEntryUrlAttribute()
    {
        if (! $this->table_tag) {
            return null;
        }

        $botNumber = \App\Models\Setting::get('whatsapp_bot_number', config('tiptap.default_whatsapp_bot_number'));
        $cleanNumber = preg_replace('/[^0-9]/', '', $botNumber);

        return 'https://wa.me/'.$cleanNumber.'?text='.urlencode($this->table_tag);
    }
}
