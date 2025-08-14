<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    /**
     * Define middleware permissions for controller actions.
     *
     * This restricts access to users with the appropriate permissions.
     *
     * @return array<int, \Illuminate\Routing\Controllers\Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view settings', only: ['index']),
            new Middleware('permission:create settings', only: ['create', 'store']),
            new Middleware('permission:edit settings', only: ['edit', 'update']),
            new Middleware('permission:delete settings', only: ['destroy']),
        ];
    }

    /**
     * The SearchService instance for reusable search/filter logic.
     *
     * @var \App\Services\SearchService
     */
    protected SearchService $searchService;

    /**
     * Inject SearchService dependency.
     *
     * @param \App\Services\SearchService $searchService
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Display a paginated list of settings, optionally filtered by search criteria.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Use SearchService to filter by 'key' (partial) and 'group' (exact)
        $query = $this->searchService->search(Setting::query(), ['key', 'group' => '='], $request);

        // Get distinct group names for filter dropdowns or UI sections
        $groups = Setting::distinct('group')->pluck('group');

        // Order settings by group and key then paginate results
        $settings = $query->orderBy('group')->orderBy('key')->paginate(10);

        return view('settings.index', compact('settings', 'groups'));
    }

    /**
     * Show the form to create a new setting.
     *
     * Passes existing groups for selection or grouping.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $groups = Setting::distinct('group')->pluck('group');
        return view('settings.form', compact('groups'));
    }

    /**
     * Store a newly created setting in the database.
     *
     * Validates input, handles image upload if needed.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:191|unique:settings,key',
            'group' => 'required|string|max:50',
            'type' => 'required|in:string,text,boolean,integer,float,array,json,image',
            'description' => 'nullable|string|max:255',
        ]);

        // Handle image file upload if the setting type is 'image'
        if ($request->type === 'image') {
            $validated['value'] = $this->handleImageUpload($request);
        } else {
            // For other types, accept value as-is (consider further type casting in model/helper)
            $validated['value'] = $request->value;
        }

        Setting::create($validated);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Show the form to edit an existing setting.
     *
     * Passes the current setting and available groups.
     *
     * @param \App\Models\Setting $setting
     * @return \Illuminate\View\View
     */
    public function edit(Setting $setting)
    {
        $groups = Setting::distinct('group')->pluck('group');
        return view('settings.form', compact('setting', 'groups'));
    }

    /**
     * Update an existing setting.
     *
     * Validates input, manages image replacement/removal when applicable.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Setting $setting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:191|unique:settings,key,' . $setting->id,
            'group' => 'required|string|max:50',
            'type' => 'required|in:string,text,boolean,integer,float,array,json,image',
            'description' => 'nullable|string|max:255',
        ]);

        if ($request->type === 'image') {
            // If user requested to remove the existing image
            if ($request->has('remove_image') && $setting->value) {
                Storage::disk('public')->delete($setting->value);
                $validated['value'] = null;
            } else {
                // Handle new image upload; fallback to existing image if no file uploaded
                // Note: field name can be customized
                $validated['value'] = $this->handleImageUpload($request, $setting, 'value');
            }
        } else {
            // If changing from image to other type, delete old image file if exists
            if ($setting->type === 'image' && $setting->value) {
                Storage::disk('public')->delete($setting->value);
            }

            // Set new value directly for non-image types
            $validated['value'] = $request->value;
        }

        $setting->update($validated);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Show bulk edit form grouped by setting groups.
     *
     * @return \Illuminate\View\View
     */
    public function bulkedit()
    {
        $groups = Setting::select('group')->distinct()->orderBy('group', 'asc')->pluck('group');
        $settings = Setting::orderBy('group')->get()->groupBy('group');

        return view('settings.bulk', compact('groups', 'settings'));
    }

    /**
     * Update multiple settings at once.
     *
     * Handles image uploads specially; other types are updated directly.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkupdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                continue; // Skip unknown keys gracefully
            }

            if ($setting->type === 'image') {
                // Compose the input field name dynamically for file input
                $fileField = "settings.$key";

                // Handle image upload or fallback to existing file path
                $setting->value = $this->handleImageUpload($request, $setting, $fileField);
                $setting->save();
                continue;
            }

            // Directly update value for non-image settings
            $setting->value = $value;
            $setting->save();
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Delete a setting.
     *
     * If the setting is an image type, also removes the associated file.
     *
     * @param \App\Models\Setting $setting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        // Remove physical image file if applicable before deleting DB record
        if ($setting->type === 'image' && $setting->value) {
            Storage::disk('public')->delete($setting->value);
        }

        // Soft or hard delete depending on your model setup
        $setting->delete();

        // Redirect back to settings index with success message
        return redirect()
            ->route('settings.index')
            ->with('success', 'Setting deleted successfully.');
    }

    /**
     * Handle image upload for a setting.
     *
     * Deletes old file if an existing setting is provided.
     * Stores and returns the new image path if a file is present.
     * Otherwise returns the current value or null.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Setting|null $setting Optional existing setting for old image removal.
     * @param string $field Input field name containing the new image file (default 'value').
     * @return string|null Path of stored image or existing value.
     */
    protected function handleImageUpload(Request $request, Setting $setting = null, string $field = 'value'): ?string
    {
        if ($request->hasFile($field)) {
            // Delete old image if exists
            if ($setting && $setting->value) {
                Storage::disk('public')->delete($setting->value);
            }

            // Store the new uploaded image in 'public/settings' directory
            return $request->file($field)->store('settings', 'public');
        }

        // No new file uploaded; keep existing path if any, else null
        return $setting->value ?? null;
    }
}
