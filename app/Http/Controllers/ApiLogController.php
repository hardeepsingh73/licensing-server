<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ApiLogController extends Controller implements HasMiddleware
{
    /**
     * Service that handles reusable search/filter functionality.
     *
     * @var \App\Services\SearchService
     */
    protected SearchService $searchService;

    /**
     * Inject dependencies into controller.
     *
     * @param  \App\Services\SearchService  $searchService
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Define route middleware permissions for this controller's actions.
     *
     * Using Laravel 10+ `HasMiddleware` + `Middleware` class approach.
     * Ensures that only users with the correct permissions can take
     * specific actions.
     *
     * @return array<int, \Illuminate\Routing\Controllers\Middleware>
     */
    public static function middleware(): array
    {
        return [
            // Only users with permission "view api logs" can view the index page
            new Middleware('permission:view api logs', only: ['index']),
            // Only users with permission "clear api logs" can clear logs
            new Middleware('permission:clear api logs', only: ['clear']),
        ];
    }
    /**
     * Display a paginated list of api logs with search capability.
     *
     * Search is done via SearchService, matching columns like:
     *  - api_type
     *  - message
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        // Use SearchService for keyword-based search
        $query = $this->searchService->search(
            ApiLog::with('user'),
            ['method' => '=', 'ip_address', 'endpoint' => '=', 'user_id' => '='],
            $request
        );

        // Paginate logs - latest first, 10 per page
        $apiLogs = $query->latest()->paginate(10);
        $users = User::orderBy('name')->get();
        return view('apilogs.index', compact('apiLogs', 'users'));
    }
    /**
     * Show details of a specific api log.
     *
     * Route Model Binding automatically fetches the apiLog instance.
     *
     * @param  \App\Models\apiLog  $apiLog
     * @return \Illuminate\View\View
     */
    public function show(ApiLog $apiLog)
    {
        return view('apilogs.view', compact('apiLog'));
    }

    /**
     * Clear (delete) all api log entries from storage.
     *
     *  This action is irreversible; use middleware to restrict access.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        ApiLog::truncate();

        return redirect()
            ->route('api-logs.index')
            ->with('success', 'All api logs have been cleared.');
    }
}
