<?php

namespace App\Http\Controllers;

use App\Http\Requests\LicenseKeyRequest;
use App\Models\LicenseKey;
use App\Models\LicenseActivation;
use App\Models\User;
use App\Services\SearchService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Class LicenseController
 *
 * Manages CRUD operations for License Keys along with license activation,
 * revocation, and device listing functionalities.
 */
class LicenseController extends Controller implements HasMiddleware
{
    /**
     * @var SearchService The SearchService for applying query filters and search queries.
     */
    protected $searchService;

    /**
     * LicenseController constructor.
     *
     * @param SearchService $searchService Dependency injection of SearchService.
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Define middleware permissions for specific controller actions.
     *
     * Restricts access based on permissions for viewing, creating,
     * editing, and deleting licenses.
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view license', only: ['index', 'show', 'listDevices']),
            new Middleware('permission:create license', only: ['create', 'store']),
            new Middleware('permission:edit license', only: ['edit', 'update', 'activateKey']),
            new Middleware('permission:delete license', only: ['destroy', 'reissueKey']),
        ];
    }

    /**
     * Display a paginated list of license keys with optional filters.
     *
     * @param Request $request HTTP request with query parameters.
     * @return \Illuminate\View\View Render the licenses index page.
     */
    public function index(Request $request)
    {
        $licenses = $this->searchService->search(
            LicenseKey::with('user'),
            [
                'key',
                'status' => '=',
                'expires_at' => '=',
                'user_id' => '=', // allow filtering by user
            ],
            $request
        )->latest()->paginate(10);

        $users = User::all(['id', 'name', 'email']);
        return view('licenses.index', compact('licenses', 'users'));
    }

    /**
     * Show the form for creating a new license key.
     *
     * @return \Illuminate\View\View Render the create form.
     */
    public function create()
    {
        $users = User::all(['id', 'name', 'email']);
        return view('licenses.form', compact('users'));
    }

    /**
     * Store a newly created license key in database.
     *
     * Employs transaction handling for data integrity.
     *
     * @param LicenseKeyRequest $request Validated request data.
     * @return RedirectResponse Redirect back to the licenses index page.
     */
    public function store(LicenseKeyRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            LicenseKey::create($request->validated());

            DB::commit();

            return redirect()->route('licenses.index')->with('success', 'License key created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display detailed information about a specific license key.
     *
     * Utilizes route model binding for clean method signature.
     *
     * @param LicenseKey $license Target license key.
     * @return \Illuminate\View\View Render the license view page.
     */
    public function show(LicenseKey $license)
    {
        $devices = $license->devices()->paginate(10);
        return view('licenses.view', compact('license', 'devices'));
    }

    /**
     * Show the form for editing an existing license key.
     *
     * Ensures the user is authorized to update before showing the form.
     *
     * @param LicenseKey $license License to be edited.
     * @return \Illuminate\View\View Render the edit form view.
     */
    public function edit(LicenseKey $license)
    {
        // $this->authorize('update', $license);
        $users = User::all(['id', 'name', 'email']);
        return view('licenses.form', compact('license', 'users'));
    }

    /**
     * Update the specified license key with new data.
     *
     * Uses transaction handling and authorization checks.
     *
     * @param LicenseKeyRequest $request Validated update data.
     * @param LicenseKey $license License being updated.
     * @return RedirectResponse Redirect back to licenses index.
     */
    public function update(LicenseKeyRequest $request, LicenseKey $license): RedirectResponse
    {
        // $this->authorize('update', $license);

        DB::beginTransaction();

        try {
            $license->update($request->validated());

            DB::commit();

            return redirect()->route('licenses.index')->with('success', 'License key updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a license key from the database.
     *
     * Authorization and transaction safe delete.
     *
     * @param LicenseKey $license License to delete.
     * @return RedirectResponse Redirect back to licenses index page.
     */
    public function destroy(LicenseKey $license): RedirectResponse
    {
        // $this->authorize('delete', $license);

        DB::beginTransaction();

        try {
            $license->delete();

            DB::commit();

            return redirect()->route('licenses.index')->with('success', 'License key deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('licenses.index')->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Activate a license key for a device.
     *
     * Validates device_id and key, enforces activation limits,
     * and records activation if new device.
     *
     * @param Request $request Activation request data.
     * @return RedirectResponse Redirect back with success or error.
     */
    public function activateKey(Request $request): RedirectResponse
    {
        $request->validate([
            'key' => 'required|string',
            'device_id' => 'required|string',
        ]);

        $license = LicenseKey::where('key', $request->input('key'))->first();

        if (!$license || !$license->isActive) {
            return redirect()->back()->withErrors(['error' => 'Invalid or inactive license key']);
        }

        if ($license->expires_at && Carbon::now()->gt($license->expires_at)) {
            return redirect()->back()->withErrors(['error' => 'License key expired']);
        }

        $deviceCount = LicenseActivation::where('license_key_id', $license->id)->count();

        if ($deviceCount >= $license->activation_limit) {
            return redirect()->back()->withErrors(['error' => 'Device activation limit reached']);
        }

        // Check if device already activated or soft deleted
        $existing = LicenseActivation::withTrashed()
            ->where('license_key_id', $license->id)
            ->where('device_id', $request->input('device_id'))
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                // Device activation was soft deleted, restore it
                $existing->restore();

                // Optionally increment activations if you track count elsewhere
                $license->increment('activations');
                return redirect()->back()->with('success', 'Device re-activated successfully');
            }

            // If not trashed, device is already activated (active)
            return redirect()->back()->withErrors(['error' => 'This Device is already activated']);
        } else {
            LicenseActivation::create([
                'license_key_id' => $license->id,
                'device_id' => $request->input('device_id'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $license->increment('activations');
        }

        return redirect()->back()->with('success', 'Activation successful');
    }

    /**
     * Revoke a license key, mark it reissued and delete all activations.
     *
     * Performs authorization and transaction-safe update.
     *
     * @param LicenseKey $license License to reissue.
     * @return RedirectResponse Redirect with status message.
     */
    public function reissueKey(LicenseKey $license): RedirectResponse
    {
        // $this->authorize('delete', $license);

        DB::beginTransaction();

        try {
            $license->status = LicenseKey::STATUS_REISSUE;
            $license->activations = 0;
            $license->save();
            $license->devices->each(function ($activation) {
                $activation->delete();
            });
            DB::commit();

            return redirect()->route('licenses.index')->with('success', 'License reissued and activations cleared.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('licenses.index')->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Check uniqueness of license key (AJAX).
     */
    public function checkKeyUniqueness(Request $request)
    {
        $key = $request->input('key');
        $exists = LicenseKey::where('key', $key)->exists();

        return response()->json(['unique' => !$exists]);
    }
}
