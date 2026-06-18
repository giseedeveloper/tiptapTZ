<?php

namespace App\Enums;

class BotAnalyticsEvent
{
    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_values(array_unique(array_merge(
            BotEngagementEvent::values(),
            BotQrEntryType::values(),
            BotFunnelStep::values(),
        )));
    }
}
