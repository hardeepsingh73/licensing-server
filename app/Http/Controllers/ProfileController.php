<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the currently authenticated user's profile edit form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * Uses a custom form request (ProfileUpdateRequest) to validate incoming data.
     * If the email is changed, the email verification timestamp is reset.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Fill only validated data into the user model
        $request->user()->fill($request->validated());

        // Reset email verification if email has been modified
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Save updates to the database
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account permanently.
     *
     * Steps:
     *  1. Validate that the password matches the current user's password.
     *  2. Log the user out from the application.
     *  3. Delete the user record from the database.
     *  4. Invalidate and regenerate session token.
     *  5. Redirect to home page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Validate with password confirmation
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log out the user
        Auth::logout();

        // Delete user account
        $user->delete();

        // Cleanup session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the current logged-in user's profile view page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function profile(Request $request): View
    {
        return view('profile.view', [
            'user' => $request->user(),
        ]);
    }
}
