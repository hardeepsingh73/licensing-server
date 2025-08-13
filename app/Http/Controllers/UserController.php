<?php

namespace App\Http\Controllers;

use App\Helpers\RoleDataHelper;
use App\Helpers\Settings;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * The SearchService for applying query filters and searching users.
     *
     * @var \App\Services\SearchService
     */
    protected $searchService;

    /**
     * Inject dependencies.
     *
     * @param \App\Services\SearchService $searchService
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Define middleware permissions for specific controller actions.
     *
     * Note: The 'edit users' permission is checked via Gate/Policy in edit/update.
     *
     * @return \Illuminate\Routing\Controllers\Middleware[]
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view users', only: ['index']),
            new Middleware('permission:create users', only: ['create', 'store']),
            new Middleware('permission:delete users', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated list of users with optional filtering by role/name/email.
     *
     * Uses RoleDataHelper to enforce role-based visibility restrictions.
     * Applies search filters via SearchService.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Fetch all roles for filter dropdown or display
        $roles = Role::orderBy('name', 'ASC')->get();

        // Get query builder with role-based user visibility from RoleDataHelper
        $query = RoleDataHelper::users(true);

        // Apply search/filter for name, email, and role relationship via SearchService
        $users = $this->searchService->search(
            $query,
            [
                'name',
                'email',
                'roles' => [
                    'relationship' => true,
                    'field' => 'id',
                    'operator' => '=',
                    'request_key' => 'role'
                ]
            ],
            $request
        )->latest()->paginate(10);

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     *
     * Roles that the current user should not see (e.g., super admins) are excluded.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $excludedRoles = [];

        // Add superadmin role to excluded roles if current user is not superadmin
        if (!auth()->user()->hasRole(Settings::get('role_super_admin', 'superadmin'))) {
            $excludedRoles[] = Settings::get('role_super_admin', 'superadmin');
        }

        // Fetch roles excluding the above
        $roles = Role::whereNotIn('name', $excludedRoles)
            ->orderBy('name', 'ASC')
            ->get();

        return view('users.form', compact('roles'));
    }

    /**
     * Store a newly created user in the database.
     *
     * Validates input, hashes password, assigns role, and uses transaction to ensure consistency.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('roles')) {
                $user->assignRole($request->roles);
            }

            DB::commit();

            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('users.create')
                ->withInput()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a user.
     *
     * Authorization enforced via UserPolicy::manage to allow only owners or authorized users.
     * Excludes super admin role from roles list unless current user is super admin.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $this->authorize('manage', $user);

        $excludedRoles = [];

        if (!auth()->user()->hasRole(Settings::get('role_super_admin', 'superadmin'))) {
            $excludedRoles[] = Settings::get('role_super_admin', 'superadmin');
        }

        $roles = Role::whereNotIn('name', $excludedRoles)
            ->orderBy('name', 'ASC')
            ->get();

        $currentRoleName = $user->getRoleNames()->first();

        return view('users.form', compact('user', 'roles', 'currentRoleName'));
    }

    /**
     * Update the specified user.
     *
     * Validates inputs, including unique email with ignoring current user.
     * Password update is optional.
     * Uses transaction and policy authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('manage', $user);

        DB::beginTransaction();

        try {
            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            if ($request->filled('roles')) {
                $user->syncRoles([$request->roles]);
            }

            DB::commit();

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('users.edit', $user->id)
                ->withInput()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove a user.
     *
     * Protects specific user IDs from deletion (e.g., system admins).
     * Uses Gate to authorize deletion.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */

    public function destroy(User $user): RedirectResponse
    {
        // Authorization check via Gate
        if (Gate::denies('delete', $user)) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Unauthorized action.');
        }

        // Policy-based authorization (if you have 'manage' policy)
        $this->authorize('manage', $user);

        // Soft delete user
        $user->delete();

        // Redirect back to index with success message
        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
