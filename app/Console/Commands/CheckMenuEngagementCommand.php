<?php

namespace App\Console\Commands;

use App\Services\MenuEngagementService;
use Illuminate\Console\Command;

class CheckMenuEngagementCommand extends Command
{
    protected $signature = 'menu-engagement:check';

    protected $description = 'Notify managers when customers viewed the menu but placed no order within the configured timeout';

    public function handle(MenuEngagementService $menuEngagementService): int
    {
        $count = $menuEngagementService->checkAndNotify();

        $this->info("Menu engagement alerts sent: {$count}");

        return self::SUCCESS;
    }
}
