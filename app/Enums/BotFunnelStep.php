<?php

namespace App\Enums;

enum BotFunnelStep: string
{
    case BotHome = 'bot_home';
    case ViewMenu = 'view_menu';
    case AddToCart = 'add_to_cart';
    case ConfirmOrder = 'confirm_order';
    case PayBill = 'pay_bill';
    case PaymentSuccess = 'payment_success';

    public function label(): string
    {
        return match ($this) {
            self::BotHome => 'Bot Home',
            self::ViewMenu => 'View Menu',
            self::AddToCart => 'Add to Cart',
            self::ConfirmOrder => 'Confirm Order',
            self::PayBill => 'Payment',
            self::PaymentSuccess => 'Success',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::BotHome => 2,
            self::ViewMenu => 3,
            self::AddToCart => 4,
            self::ConfirmOrder => 5,
            self::PayBill => 6,
            self::PaymentSuccess => 7,
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<self>
     */
    public static function orderedSteps(): array
    {
        return [
            self::BotHome,
            self::ViewMenu,
            self::AddToCart,
            self::ConfirmOrder,
            self::PayBill,
            self::PaymentSuccess,
        ];
    }
}
