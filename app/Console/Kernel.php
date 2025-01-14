<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * The maximum number of times package discovery can run recursively.
     */
    protected $maxDiscoveryAttempts = 3;

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $billingService = new BillingService();
            $billingService->processRecurringBilling();
        })->daily();

        $schedule->command('invoices:send-reminders')->daily();
        $schedule->command('invoices:process-reminders')->daily();
      
        $schedule->command('audit:prune')->daily();

        $schedule->call(function () {
            try {
                $reports = Report::query()
                    ->whereNotNull('schedule')
                    ->where(function ($query) {
                        $query->whereNull('last_generated_at')
                            ->orWhere('last_generated_at', '<=', now()->subHour());
                    })
                    ->get();

                foreach ($reports as $report) {
                    try {
                        if ($this->shouldGenerateReport($report)) {
                            app(ReportGenerationService::class)->generateReport($report);
                            $report->update([
                                'last_generated_at' => now(),
                                'last_generation_status' => 'success'
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to generate report: ' . $e->getMessage(), [
                            'report_id' => $report->id,
                            'error' => $e->getMessage()
                        ]);
                        $report->update([
                            'last_generation_status' => 'failed',
                            'last_error' => $e->getMessage()
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to process report generation schedule: ' . $e->getMessage());
            }
        })->hourly();
    }

    protected function shouldGenerateReport(Report $report): bool
    {
        if (!$report->last_generated_at) {
            return true;
        }

        if (!isset($report->schedule['frequency'])) {
            return false;
        }

        $schedule = $report->schedule;
        $lastGenerated = $report->last_generated_at;

        return match($schedule['frequency']) {
            'daily' => $lastGenerated->diffInDays(now()) >= 1,
            'weekly' => $lastGenerated->diffInWeeks(now()) >= 1,
            'monthly' => $lastGenerated->diffInMonths(now()) >= 1,
            'hourly' => $lastGenerated->diffInHours(now()) >= 1,
            default => false
        };
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Add discovery loop protection
        $attemptKey = 'package_discovery_attempts';
        $attempts = Cache::get($attemptKey, 0);

        if ($attempts >= $this->maxDiscoveryAttempts) {
            Cache::forget($attemptKey);
            throw new \RuntimeException('Package discovery maximum attempts exceeded. Possible circular dependency detected.');
        }

        Cache::put($attemptKey, $attempts + 1, 60);

        try {
            $this->load(__DIR__.'/Commands');
            require base_path('routes/console.php');
        } finally {
            Cache::forget($attemptKey);
        }
    }
}

