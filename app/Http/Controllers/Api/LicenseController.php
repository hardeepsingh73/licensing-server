<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LicenseKeyRequest;
use App\Models\LicenseActivation;
use App\Models\LicenseKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller implements HasMiddleware
{
    /**
     * Define middleware permissions for specific controller actions.
     *
     * @return Middleware[]
     */
    public static function middleware(): array
    {
        return [
            // Add permissions here if needed
            // new Middleware('permission:validate key', only: ['validateKey']),
            new Middleware('permission:activate key', only: ['activateKey']),
            new Middleware('permission:reissue key', only: ['reissueKey']),
            // new Middleware('permission:list devices', only: ['listDevices']),
        ];
    }

    /**
     * Validate a license key's status and expiry.
     */
    public function validateKey(Request $request)
    {
        $license = $this->getValidLicense($request->input('key'));

        if (!$license['success']) {
            return response()->json($license, $license['status_code']);
        }

        return response()->json([
            'success' => true,
            'message' => 'License is valid',
            'data' => [
                'key' => $license['data']->key,
                'status' => $license['data']->status_label,
                'expires_at' => $license['data']->expires_at,
                'activation_limit' => $license['data']->activation_limit,
                'activations' => $license['data']->activations,
                'user' => optional($license['data']->user)->only(['id', 'name', 'email']),
            ]
        ], 200);
    }

    /**
     * Activate a license key for a device, enforcing device limits.
     */
    public function activateKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'device_id' => 'required|string',
        ]);

        $license = $this->getValidLicense($request->input('key'));

        if (!$license['success']) {
            return response()->json($license, $license['status_code']);
        }

        $license = $license['data'];

        // Check if device already activated
        $existing = LicenseActivation::where('license_key_id', $license->id)
            ->where('device_id', $request->input('device_id'))
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This device is already activated'
            ], 409);
        }

        // Check device limit
        $deviceCount = LicenseActivation::where('license_key_id', $license->id)->count();
        if ($deviceCount >= $license->activation_limit) {
            return response()->json([
                'success' => false,
                'message' => 'Device activation limit reached'
            ], 403);
        }

        LicenseActivation::create([
            'license_key_id' => $license->id,
            'device_id' => $request->input('device_id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $license->increment('activations');

        return response()->json([
            'success' => true,
            'message' => 'Activation successful'
        ], 200);
    }

    /**
     * Revoke & reissue a license key: changes status to reissued and clears activations.
     */
    public function reissueKey(Request $request)
    {
        $key = $request->input('key');
        $license = LicenseKey::where('key', $key)->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License key not found'
            ], 404);
        }

        DB::transaction(function () use ($license) {
            $license->status = LicenseKey::STATUS_REISSUE;
            $license->activations = 0;
            $license->save();
            $license->devices->each(function ($activation) {
                $activation->delete();
            });
        });

        return response()->json([
            'success' => true,
            'message' => 'License reissued and activations cleared'
        ]);
    }

    /**
     * List devices activated under a license key.
     */
    public function listDevices(Request $request)
    {
        $key = $request->input('key');
        $license = LicenseKey::where('key', $key)->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License key not found',
                'data' => []
            ], 404);
        }

        $devices = $license->devices()->select('device_id', 'ip_address', 'user_agent', 'created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Devices fetched successfully',
            'data' => $devices
        ]);
    }

    /**
     * Create a new license key.
     */
    public function createKey(LicenseKeyRequest $request)
    {
        DB::beginTransaction();

        try {
            $licenseKey = LicenseKey::create($request->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'License key created successfully',
                'data' => $licenseKey
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create license key',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Validate license and return standardized response array.
     */
    private function getValidLicense(string $key): array
    {
        $license = LicenseKey::with('user')->where('key', $key)->first();

        if (!$license) {
            return [
                'success' => false,
                'message' => 'License key not found',
                'status_code' => 404
            ];
        }

        if (!$license->isActive) {
            return [
                'success' => false,
                'message' => 'License is inactive',
                'status_code' => 403
            ];
        }

        if ($license->expires_at && Carbon::now()->gt($license->expires_at)) {
            return [
                'success' => false,
                'message' => 'License expired',
                'status_code' => 403
            ];
        }

        return [
            'success' => true,
            'message' => 'Valid license',
            'status_code' => 200,
            'data' => $license
        ];
    }
}
