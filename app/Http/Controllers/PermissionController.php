<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller implements HasMiddleware
{
    /**
     * Define middleware permissions for controller actions.
     *
     * This ensures that users must have the appropriate permission
     * to access specific controller methods.
     * 
     * @return array<int, \Illuminate\Routing\Controllers\Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view permissions', only: ['index']),
            new Middleware('permission:edit permissions', only: ['edit', 'update']),
            new Middleware('permission:create permissions', only: ['create', 'store']),
            new Middleware('permission:delete permissions', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated list of permissions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $permissions = Permission::orderBy('created_at', 'DESC')->paginate(10);
        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show form for creating a new permission.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('permissions.form');
    }

    /**
     * Store a newly created permission in storage.
     *
     * Validates the input, ensuring name is unique and has minimum length.
     *
     * @param  \App\Http\Requests\PermissionRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PermissionRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $permission = Permission::create(['name' => $request->name]);

            if (!$permission) {
                throw new \Exception('Failed to create permission');
            }

            DB::commit();
            return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('permissions.index')->with('error', 'Permission creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing an existing permission.
     *
     * Uses route model binding.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return \Illuminate\View\View
     */
    public function edit(Permission $permission)
    {
        return view('permissions.form', compact('permission'));
    }

    /**
     * Update an existing permission.
     *
     * Validates the input to maintain uniqueness of the permission name,
     * except for the current permission's ID.
     *
     * @param  int  $id
     * @param  \App\Http\Requests\PermissionRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $updated = $permission->update(['name' => $request->name]);

            if (!$updated) {
                throw new \Exception('Failed to update permission');
            }

            DB::commit();
            return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('permissions.index')->with('error', 'Permission update failed: ' . $e->getMessage());
        }
    }
    /**
     * Delete a permission.
     *
     * Checks authorization via a Gate before deleting.
     * Returns JSON response indicating success or failure.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return \Illuminate\Http\RedirectResponse
     */

    public function destroy(Permission $permission): RedirectResponse
    {
        // Ensure the current user is authorized to delete the permission
        if (Gate::denies('delete', $permission)) {
            return redirect()
                ->route('permissions.index')
                ->with('error', 'Unauthorized action.');
        }

        DB::beginTransaction();

        try {
            $deleted = $permission->delete();

            if (!$deleted) {
                throw new \Exception('Failed to delete permission');
            }

            DB::commit();
            return redirect()
                ->route('permissions.index')
                ->with('success', 'Permission deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('permissions.index')
                ->with('error', 'Permission deletion failed: ' . $e->getMessage());
        }
    }
}
