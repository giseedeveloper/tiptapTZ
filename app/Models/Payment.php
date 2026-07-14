<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'restaurant_id',
        'waiter_id',
        'tip_pool_id',
        'customer_phone',
        'amount',
        'method',
        'payment_type',
        'status',
        'transaction_reference',
        'description',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    protected function transactionId(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->transaction_reference);
    }

    protected function paymentMethod(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->method);
    }
}
