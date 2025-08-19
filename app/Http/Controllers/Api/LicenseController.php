<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LicenseActivation;
use App\Models\LicenseKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
            // new Middleware('permission:validate key', only: ['validateKey']),
            new Middleware('permission:activate key', only: ['activateKey']),
            new Middleware('permission:revoke key', only: ['revokeKey']),
            // new Middleware('permission:list devices', only: ['listDevices']),
        ];
    }

    /**
     * Validate a license key's status and expiry.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateKey(Request $request)
    {
        $key = $request->input('key');
        $license = LicenseKey::where('key', $key)->first();

        if (!$license || $license->status !== LicenseKey::STATUS_ACTIVE) {
            return response()->json(['valid' => false, 'message' => 'Invalid or inactive key'], 403);
        }

        if ($license->expires_at && Carbon::now()->gt($license->expires_at)) {
            return response()->json(['valid' => false, 'message' => 'Key expired'], 403);
        }

        return response()->json(['valid' => true]);
    }

    /**
     * Activate a license key for a device, enforcing device limits.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateKey(Request $request)
    {
        $key = $request->input('key');
        $deviceId = $request->input('device_id');
        $license = LicenseKey::where('key', $key)->first();

        if (!$license || $license->status !== LicenseKey::STATUS_ACTIVE) {
            return response()->json(['activated' => false, 'message' => 'Invalid key'], 403);
        }
        if ($license->expires_at && Carbon::now()->gt($license->expires_at)) {
            return response()->json(['activated' => false, 'message' => 'License expired'], 403);
        }

        $deviceCount = LicenseActivation::where('license_key_id', $license->id)->count();
        if ($deviceCount >= $license->activation_limit) {
            return response()->json(['activated' => false, 'message' => 'Device limit reached'], 403);
        }

        $existing = LicenseActivation::where('license_key_id', $license->id)
            ->where('device_id', $deviceId)
            ->first();

        if (!$existing) {
            LicenseActivation::create([
                'license_key_id' => $license->id,
                'device_id' => $deviceId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $license->increment('activations');
        }

        return response()->json(['activated' => true, 'message' => 'Activation successful']);
    }

    /**
     * Revoke a license key: changes status to revoked and clears activations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeKey(Request $request)
    {
        $key = $request->input('key');
        $license = LicenseKey::where('key', $key)->first();

        if (!$license) {
            return response()->json(['revoked' => false, 'message' => 'License key not found'], 404);
        }

        $license->status = LicenseKey::STATUS_REVOKED;
        $license->save();

        $license->activations()->delete();

        return response()->json(['revoked' => true, 'message' => 'License revoked and activations cleared']);
    }

    /**
     * List devices activated under a license key.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listDevices(Request $request)
    {
        $key = $request->input('key');
        $license = LicenseKey::where('key', $key)->first();

        if (!$license) {
            return response()->json(['devices' => [], 'message' => 'License key not found'], 404);
        }

        $devices = $license->activations()
            ->select('device_id', 'ip_address', 'user_agent', 'created_at')
            ->get();

        return response()->json(['devices' => $devices]);
    }
}
