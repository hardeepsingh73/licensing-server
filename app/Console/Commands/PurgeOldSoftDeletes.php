<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ApiLog;
use App\Models\EmailLog;
use App\Models\ErrorLog;
use App\Models\File;
use App\Models\LicenseActivation;
use App\Models\LicenseKey;
use App\Models\LoginHistory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeOldSoftDeletes extends Command
{
    protected $signature = 'models:purge-old-soft-deletes';

    protected $description = 'Permanently delete soft deleted models older than 30 days';

    // List your models here
    protected $models = [
        ApiLog::class,
        ActivityLog::class,
        EmailLog::class,
        ErrorLog::class,
        File::class,
        LicenseKey::class,
        LicenseActivation::class,
        LoginHistory::class,
        Permission::class,
        Role::class,
        Setting::class,
        User::class,
    ];

    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(30);

        foreach ($this->models as $modelClass) {
            $modelName = class_basename($modelClass);
            $this->info("Purging $modelName soft deletes older than 30 days...");

            $deletedRecords = $modelClass::onlyTrashed()
                ->where('deleted_at', '<=', $cutoffDate)
                ->get();

            $count = $deletedRecords->count();

            if ($count > 0) {
                foreach ($deletedRecords as $record) {
                    $record->forceDelete();
                }
                $this->info("Deleted $count $modelName records permanently.");
            } else {
                $this->info("No $modelName soft deletes older than 30 days found.");
            }
        }

        $this->info('Purge completed.');

        return Command::SUCCESS;
    }
}
