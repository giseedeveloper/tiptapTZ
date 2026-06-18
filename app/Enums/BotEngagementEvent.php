<?php

namespace App\Enums;

enum BotEngagementEvent: string
{
    case ViewMenu = 'view_menu';
    case CallWaiter = 'call_waiter';
    case PayBill = 'pay_bill';
    case RateService = 'rate_service';
    case GiveTips = 'give_tips';
    case ChangeLanguage = 'change_language';
    case ExitBot = 'exit_bot';

    public function label(): string
    {
        return match ($this) {
            self::ViewMenu => 'View Menu',
            self::CallWaiter => 'Call Waiter',
            self::PayBill => 'Pay Bill',
            self::RateService => 'Rate Service',
            self::GiveTips => 'Give Tips',
            self::ChangeLanguage => 'Change Language',
            self::ExitBot => 'Exit Bot',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ViewMenu => '#8C71F6',
            self::CallWaiter => '#f59e0b',
            self::PayBill => '#10b981',
            self::RateService => '#06b6d4',
            self::GiveTips => '#ec4899',
            self::ChangeLanguage => '#6D52E8',
            self::ExitBot => '#f43f5e',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
