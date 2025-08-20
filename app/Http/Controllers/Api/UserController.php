<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
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
            new Middleware('permission:view users', only: ['index', 'show']),
            new Middleware('permission:create users', only: ['store']),
            new Middleware('permission:edit users', only: ['update']),
            new Middleware('permission:delete users', only: ['destroy']),
        ];
    }
    /**
     * Display a paginated list of users.
     *
     * @param  App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles')->latest()->paginate(10);

        return $this->successResponse($users);
    }

    /**
     * Store a newly created user in the database.
     *
     * @param  App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
            ]);

            if ($request->filled('roles')) {
                $this->assignUserRole($user, $request->validated('roles'));
            }

            DB::commit();

            return $this->successResponse(
                $user->load('roles'),
                'User created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified user.
     *
     * Uses Route Model Binding for cleaner code.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return $this->successResponse($user->load('roles'));
    }

    /**
     * Update the specified user in the database.
     *
     * @param  App\Http\Requests\UserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        DB::beginTransaction();

        try {
            $user->update([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->filled('password')
                    ? Hash::make($request->validated('password'))
                    : $user->password
            ]);

            if ($request->filled('roles')) {
                $this->syncUserRoles($user, $request->validated('roles'));
            }

            DB::commit();

            return $this->successResponse(
                $user->fresh('roles'),
                'User updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Remove the specified user from the database.
     *
     * User deletion is restricted for certain protected accounts.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($this->isProtectedUser($user)) {
            return $this->errorResponse('Protected user cannot be deleted', 403);
        }

        DB::beginTransaction();

        try {
            $user->delete();
            DB::commit();

            return $this->successResponse(
                null,
                'User deleted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    protected function assignUserRole(User $user, string $role): void
    {
        if (!Role::where('name', $role)->exists()) {
            throw new \Exception("The specified role does not exist");
        }

        if (!$user->assignRole($role)) {
            throw new \Exception("Failed to assign role to user");
        }
    }

    protected function syncUserRoles(User $user, string $role): void
    {
        if (!Role::where('name', $role)->exists()) {
            throw new \Exception("The specified role does not exist");
        }

        if (!$user->syncRoles([$role])) {
            throw new \Exception("Failed to update user roles");
        }
    }

    protected function isProtectedUser(User $user): bool
    {
        return in_array($user->id, [1, 4]); // Protected user IDs
    }

    protected function successResponse(
        mixed $data = null,
        string $message = '',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $status);
    }

    protected function errorResponse(
        string $message,
        int $status = 500
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
