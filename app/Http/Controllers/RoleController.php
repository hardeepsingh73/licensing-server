<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

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
        return view('roles.index', compact('roles'));
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
        return view('roles.form', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     *
     * Validate input, create the role, and assign selected permissions.
     *
     * @param  App\Http\Requests\RoleRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Create the role
            $role = Role::create(['name' => $request->name]);

            if (!$role) {
                throw new \Exception('Failed to create role');
            }

            // Assign permissions if provided
            if ($request->filled('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)
                    ->pluck('name')
                    ->toArray();

                $role->syncPermissions($permissions);
            }

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('roles.index')->with('error', 'Role creation failed: ' . $e->getMessage());
        }
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
        $permissions = Permission::all();
        $hasPermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.form', compact('role', 'permissions', 'hasPermissions'));
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
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Update role name
            $role->name = $request->name;
            $saved = $role->save();

            if (!$saved) {
                throw new \Exception('Failed to update role');
            }

            // Sync permissions
            $permissions = $request->filled('permissions')
                ? Permission::whereIn('id', $request->permissions)->pluck('name')->toArray()
                : [];

            $role->syncPermissions($permissions);

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('roles.index')->with('error', 'Role update failed: ' . $e->getMessage());
        }
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

        DB::beginTransaction();

        try {
            $deleted = $role->delete();

            if (!$deleted) {
                throw new \Exception('Failed to delete role');
            }

            DB::commit();
            return redirect()
                ->route('roles.index')
                ->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('roles.index')
                ->with('error', 'Role deletion failed: ' . $e->getMessage());
        }
    }
}
