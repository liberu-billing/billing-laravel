<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\BillingService;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $billingService = new BillingService();
            $billingService->processRecurringBilling();
        })->daily();

        $schedule->call(function () {
            $billingService = new BillingService();
            $billingService->sendOverdueReminders();
        })->daily();

        $schedule->call(function () {
            $reports = Report::whereNotNull('schedule')->get();
            foreach ($reports as $report) {
                if ($this->shouldGenerateReport($report)) {
                    app(ReportGenerationService::class)->generateReport($report);
                    $report->update(['last_generated_at' => now()]);
                }
            }
        })->hourly();
    }

    protected function shouldGenerateReport(Report $report): bool
    {
        if (!$report->last_generated_at) {
            return true;
        }

        $schedule = $report->schedule;
        return match($schedule['frequency']) {
            'daily' => $report->last_generated_at->diffInDays(now()) >= 1,
            'weekly' => $report->last_generated_at->diffInWeeks(now()) >= 1,
            'monthly' => $report->last_generated_at->diffInMonths(now()) >= 1,
            default => false
        };
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}


<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('audit:prune')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}