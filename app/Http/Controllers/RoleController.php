<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller implements HasMiddleware
{
    /**
     * Define permissions-based middleware for controller actions.
     *
     * This ensures that users must have the specified permissions
     * to access each method.
     *
     * @return array<int, \Illuminate\Routing\Controllers\Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view roles', only: ['index']),
            new Middleware('permission:edit roles', only: ['edit', 'update']),
            new Middleware('permission:create roles', only: ['create', 'store']),
            new Middleware('permission:delete roles', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated listing of roles.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Retrieve roles ordered by name ascending and paginate 10 per page
        $roles = Role::orderBy('name', 'ASC')->paginate(10);

        return view('roles.index', [
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new role.
     *
     * Pass all permissions to the view so they can be assigned to the new role.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Retrieve all permissions to show as assignable options
        $permissions = Permission::all();

        return view('roles.form', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role in storage.
     *
     * Validate input, create the role, and assign selected permissions.
     *
     * @param  App\Http\Requests\RoleRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);

        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $role->givePermissionTo($permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role added successfully.');
    }


    /**
     * Show the form for editing the specified role.
     *
     * Pass current role's permissions and all permissions to the view.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\View\View
     */
    public function edit(Role $role)
    {
        // Get names of permissions assigned to this role
        $hasPermissions = $role->permissions->pluck('name');

        // Retrieve all permissions for selection
        $permissions = Permission::all();

        return view('roles.form', [
            'permissions'    => $permissions,
            'hasPermissions' => $hasPermissions,
            'role'           => $role,
        ]);
    }

    /**
     * Update the specified role in storage.
     *
     * Validates input, updates the role name and synchronizes permissions.
     *
     * @param  int  $id
     * @param  App\Http\Requests\RoleRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RoleRequest $request, Role $role)
    {
        $role->name = $request->name;
        $role->save();

        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     *
     * Checks authorization via Gate before deleting.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Authorization check: deny if user can't delete role
        if (Gate::denies('delete', $role)) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'Unauthorized action.');
        }

        // Delete the role
        $role->delete();

        // Redirect with success message
        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
