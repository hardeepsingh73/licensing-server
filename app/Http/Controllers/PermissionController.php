<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

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
            new Middleware('permission:edit permissions', only: ['edit']),
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
        // Retrieve permissions ordered newest first
        $permissions = Permission::orderBy('created_at', 'DESC')->paginate(10);

        // Render the index view with permissions data
        return view('permissions.index', ['permissions' => $permissions]);
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
    public function store(PermissionRequest $request)
    {
        Permission::create(['name' => $request->name]);
        return redirect()->route('permissions.index')->with('success', 'Permission added successfully.');
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
        return view('permissions.form', ['permission' => $permission]);
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
    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update(['name' => $request->name]);
        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
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
                ->route('permission.index')
                ->with('error', 'Unauthorized action.');
        }

        $permission->delete();

        // Redirect with success message
        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
