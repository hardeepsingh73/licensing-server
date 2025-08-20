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
            // You can enable / adjust as needed if API endpoints are protected by permissions
            // new Middleware('permission:validate key', only: ['validateKey']),
            new Middleware('permission:activate key', only: ['activateKey']),
            new Middleware('permission:reissue key', only: ['reissueKey']),
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
        $license = LicenseKey::with('user')->where('key', $key)->first();

        if (!$license || $license->status !== LicenseKey::STATUS_ACTIVE) {
            return response()->json(['valid' => false, 'message' => 'Invalid or inactive key'], 403);
        }

        if ($license->expires_at && Carbon::now()->gt($license->expires_at)) {
            return response()->json(['valid' => false, 'message' => 'Key expired'], 403);
        }

        return response()->json([
            'valid' => true,
            'license' => [
                'key' => $license->key,
                'status' => $license->status_label,
                'expires_at' => $license->expires_at,
                'activation_limit' => $license->activation_limit,
                'activations' => $license->activations,
                'user' => $license->user ? [
                    'id' => $license->user->id,
                    'name' => $license->user->name,
                    'email' => $license->user->email
                ] : null,
            ]
        ]);
    }

    /**
     * Activate a license key for a device, enforcing device limits.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'device_id' => 'required|string',
        ]);

        $license = LicenseKey::where('key', $request->input('key'))->first();

        if (!$license || $license->status !== LicenseKey::STATUS_ACTIVE) {
            return response()->json(['activated' => false, 'message' => 'Invalid or inactive key'], 403);
        }
        if ($license->expires_at && Carbon::now()->gt($license->expires_at)) {
            return response()->json(['activated' => false, 'message' => 'License expired'], 403);
        }

        $deviceCount = LicenseActivation::where('license_key_id', $license->id)->count();
        if ($deviceCount >= $license->activation_limit) {
            return response()->json(['activated' => false, 'message' => 'Device activation limit reached'], 403);
        }

        $existing = LicenseActivation::where('license_key_id', $license->id)
            ->where('device_id', $request->input('device_id'))
            ->first();

        if ($existing) {
            return response()->json(['activated' => false, 'message' => 'This Device is already activated'], 403);
        } else {
            LicenseActivation::create([
                'license_key_id' => $license->id,
                'device_id' => $request->input('device_id'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $license->increment('activations');
        }

        return response()->json(['activated' => true, 'message' => 'Activation successful']);
    }

    /**
     * Revoke a license key: changes status to reissued and clears activations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reissueKey(Request $request)
    {
        $key = $request->input('key');
        $license = LicenseKey::where('key', $key)->first();

        if (!$license) {
            return response()->json(['reissued' => false, 'message' => 'License key not found'], 404);
        }

        $license->status = LicenseKey::STATUS_REISSUE;
        $license->save();

        $license->activations()->delete();

        return response()->json(['reissued' => true, 'message' => 'License reissued and activations cleared']);
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
