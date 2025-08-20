<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ApiLog;
use App\Models\EmailLog;
use App\Models\ErrorLog;
use App\Models\LoginHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeOldLogs extends Command
{
    protected $signature = 'logs:purge-old';

    protected $description = 'Permanently deleted logs older than 3 months';

    protected $logModels = [
        ApiLog::class,
        ActivityLog::class,
        EmailLog::class,
        ErrorLog::class,
        LoginHistory::class,
    ];

    public function handle()
    {
        $cutoffDate = Carbon::now()->subMonths(3);
        foreach ($this->logModels as $modelClass) {
            $logModelName = class_basename($modelClass);
            $this->info("Purging $logModelName Logs older than 3 months...");

            $logs = $modelClass::where('created_at', '<=', $cutoffDate)->get();

            $count = $logs->count();

            if ($count > 0) {
                foreach ($logs as $log) {
                    $log->forceDelete();
                }
                $this->info("Deleted $count $logModelName records permanently.");
            } else {
                $this->info("No $logModelName logs older than 3 months found.");
            }
        }
        $this->info('Log purge completed.');

        return Command::SUCCESS;
    }
}
