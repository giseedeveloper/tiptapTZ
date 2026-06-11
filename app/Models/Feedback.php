<?php

namespace App\Models;

use App\Enums\FeedbackType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['restaurant_id', 'order_id', 'waiter_id', 'type', 'rating', 'comment'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\RestaurantScope);
    }

    protected function casts(): array
    {
        return [
            'type' => FeedbackType::class,
        ];
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function scopeForService(Builder $query): Builder
    {
        return $query->whereIn('type', [
            FeedbackType::Waiter->value,
            FeedbackType::Restaurant->value,
        ]);
    }

    public function scopeForFood(Builder $query): Builder
    {
        return $query->where('type', FeedbackType::Food->value);
    }

    public function scopeForWaiter(Builder $query): Builder
    {
        return $query->where('type', FeedbackType::Waiter->value);
    }
}
