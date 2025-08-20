<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShadowLoginController extends Controller
{
    /**
     * Log in as another user (impersonation).
     *
     * This method stores the currently authenticated admin's ID in the session
     * so you can revert later, then logs in as the specified target user.
     *
     *  SECURITY NOTE:
     *   - Restrict this route using middleware/permission checks (e.g., only super admins).
     *   - Always log impersonation actions for audit purposes.
     *
     * @param  \App\Models\User  $user  The user to impersonate.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAsUser(User $user)
    {
        // Save the original admin's user ID to session
        session()->put('shadow_admin_id', Auth::id());

        // Log in as the target user
        Auth::login($user);

        // Redirect to dashboard as impersonated user
        return redirect('/dashboard')->with('status', 'Logged in as user: ' . $user->name);
    }

    /**
     * Revert back to the original user after impersonation.
     *
     * Reads the saved admin ID from session, logs in as them,
     * and removes the shadow session key.
     *
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function revertBack()
    {
        // Ensure we have a stored shadow session
        if (!session()->has('shadow_admin_id')) {
            abort(403, 'No shadow session found');
        }

        // Retrieve and remove the stored admin ID
        $adminId = session()->pull('shadow_admin_id');

        // Find original admin user in DB
        $admin = User::find($adminId);

        if ($admin) {
            // Log back in as the admin
            Auth::login($admin);

            return redirect('/dashboard')
                ->with('status', 'Reverted to Super Admin');
        }

        abort(403, 'Original user not found');
    }
}
