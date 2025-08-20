<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordController extends Controller
{
    /**
     * Display the change password form.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle the change password request.
     *
     * Steps:
     *  1. Validate incoming request data.
     *  2. Check if the current password matches the authenticated user's password.
     *  3. If it matches, hash & update the new password.
     *  4. Redirect back with success message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException  If current password is incorrect.
     */
    public function changePassword(Request $request)
    {
        //  Step 1: Validate user input
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        // Get the currently authenticated user
        $user = Auth::user();

        //  Step 2: Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match our records.'],
            ]);
        }

        //  Step 3: Update password with hashing
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        //  Step 4: Redirect to dashboard with success message
        return redirect()->route('dashboard')->with('status', 'Password updated successfully!');
    }
}
