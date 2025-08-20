<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LoginHistoryController extends Controller implements HasMiddleware
{
    /**
     * Search service instance for handling queries with filters.
     *
     * @var \App\Services\SearchService
     */
    protected SearchService $searchService;

    /**
     * Inject SearchService dependency.
     *
     * @param  \App\Services\SearchService  $searchService
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Define permission middleware for controller actions.
     *
     * @return array<int, \Illuminate\Routing\Controllers\Middleware>
     */
    public static function middleware(): array
    {
        return [
            // Only users with view permission can see the logs
            new Middleware('permission:view login history', only: ['index']),
        ];
    }

    /**
     * Display a paginated list of all users' login histories with optional search filters.
     *
     * Filters (passed as query params):
     *  - ip_address : match by IP address (partial match)
     *  - login_at   : filter by login timestamp/date
     *  - user_id    : filter by exact user ID
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        // Get all users list for use in filter dropdowns in the view
        $users = User::orderBy('name')->get();

        // Build search query for login history logs
        $query = $this->searchService->search(
            LoginHistory::with('user'),
            ['ip_address', 'login_at', 'user_id' => '='],
            $request
        );

        // Order by latest login time and paginate
        $histories = $query->latest('login_at')->paginate(10);

        return view('login-history.index', compact('histories', 'users'));
    }

    /**
     * Display the currently authenticated user's own login history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function myHistory(Request $request): View
    {
        // Search within the current user's login history
        $query = $this->searchService->search(
            $request->user()->loginHistories(),
            ['ip_address', 'login_at'],
            $request
        );

        $histories = $query->latest('login_at')->paginate(10);

        return view('login-history.personal', compact('histories'));
    }

    /**
     * Clear all login history logs from the database.
     *
     *  This action is irreversible.
     * Should be restricted to only authorized administrators.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        LoginHistory::truncate();

        return redirect()->route('login-history.index')->with('success', 'All login history logs have been cleared.');
    }
}
