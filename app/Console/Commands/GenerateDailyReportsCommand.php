<?php

namespace App\Console\Commands;

use App\Services\DailyReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyReportsCommand extends Command
{
    protected $signature = 'daily-reports:generate
                            {--date= : Report date (Y-m-d). Defaults to yesterday}
                            {--restaurant= : Limit to a restaurant id}
                            {--force : Regenerate even if files already exist}';

    protected $description = 'Auto-generate exportable PDF/Excel daily reports (orders, revenue, AOV, customers, items, waiters, turnover, peak hours)';

    public function handle(DailyReportService $dailyReports): int
    {
        $date = $this->option('date')
            ? Carbon::parse((string) $this->option('date'))->startOfDay()
            : now()->subDay()->startOfDay();

        $force = (bool) $this->option('force');
        $restaurantId = $this->option('restaurant');

        if ($restaurantId) {
            $restaurant = \App\Models\Restaurant::query()->findOrFail((int) $restaurantId);
            $report = $dailyReports->generate(
                $restaurant,
                $date,
                \App\Models\DailyReport::SOURCE_SCHEDULED,
                $force,
            );

            $this->info("Generated daily report #{$report->id} for {$restaurant->name} on {$date->toDateString()}");

            return self::SUCCESS;
        }

        $result = $dailyReports->generateForAllActiveRestaurants($date, $force);
        $this->info("Daily reports generated: {$result['generated']}, skipped: {$result['skipped']} ({$date->toDateString()})");

        return self::SUCCESS;
    }
}
