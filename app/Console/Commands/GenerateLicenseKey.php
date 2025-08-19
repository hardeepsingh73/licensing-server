<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\LicenseKey;

class GenerateLicenseKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate {limit=3 : Activation limit for this key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new license key with an optional activation limit';


    /**
     * Execute the console command.
     */

    public function handle()
    {
        $activationLimit = (int) $this->argument('limit');

        $key = 'IPTV-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

        LicenseKey::create([
            'key' => $key,
            'activation_limit' => $activationLimit,
        ]);

        $this->info("License Key Generated: {$key}");
        $this->info("Activation Limit: {$activationLimit}");
    }
}
