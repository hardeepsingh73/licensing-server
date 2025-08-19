<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\LicenseKey;
use App\Models\User;

class GenerateLicenseKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example: php artisan license:generate {limit} {--user=}
     */
    protected $signature = 'license:generate 
                            {limit=3 : Activation limit for this key} 
                            {--user= : User ID to assign this license}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a new license key with an optional activation limit and user assignment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activationLimit = (int) $this->argument('limit');
        $userId = $this->option('user');

        // Generate random license key
        $key = 'IPTV-' . strtoupper(Str::random(4)) . '-'
            . strtoupper(Str::random(4)) . '-'
            . strtoupper(Str::random(4));

        // Validate user if provided
        $user = null;
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return;
            }
        }

        // Create license key
        $license = LicenseKey::create([
            'key' => $key,
            'activation_limit' => $activationLimit,
            'user_id' => $userId ?: null,
        ]);

        // Output result
        $this->info("✅ License Key Generated: {$license->key}");
        $this->info("🔑 Activation Limit: {$activationLimit}");
        if ($user) {
            $this->info("👤 Assigned to User: {$user->name} ({$user->email})");
    } else {
            $this->info("👤 Assigned to: none");
        }
    }
}
