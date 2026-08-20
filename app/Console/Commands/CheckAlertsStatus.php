<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Alert;
use Carbon\Carbon;

class CheckAlertsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update status of alerts based on expiry date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        $this->info("Checking alerts for {$today->toDateString()}...");

        // 1. Any alert where expiry_date <= today should become 'Expired'
        $expiredCount = Alert::where('status', '!=', 'Expired')
            ->whereDate('expiry_date', '<=', $today)
            ->update(['status' => 'Expired']);

        // 2. Any alert where status is 'Active' and now is within alert_days_before window should become 'Warning'
        $activeAlerts = Alert::where('status', 'Active')->get();
        $warningCount = 0;

        foreach ($activeAlerts as $alert) {
            $thresholdDate = Carbon::parse($alert->expiry_date)->subDays($alert->alert_days_before);
            if ($today->greaterThanOrEqualTo($thresholdDate)) {
                $alert->status = 'Warning';
                $alert->save();
                $warningCount++;
            }
        }

        $this->info("Done! {$expiredCount} alerts marked as Expired, {$warningCount} alerts marked as Warning.");
    }
}
