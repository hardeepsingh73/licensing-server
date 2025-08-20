<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ErrorLogController extends Controller implements HasMiddleware
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
            // Only users with permission "view error logs" can view the index page
            new Middleware('permission:view error logs', only: ['index']),
            // Only users with permission "clear error logs" can clear logs
            new Middleware('permission:clear error logs', only: ['clear']),
        ];
    }

    /**
     * Display a paginated list of error logs with search capability.
     *
     * Search is done via SearchService, matching columns like:
     *  - error_type
     *  - message
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        // Use SearchService for keyword-based search
        $query = $this->searchService->search(
            ErrorLog::query(),
            ['error_type', 'message'],
            $request
        );

        // Paginate logs - latest first, 10 per page
        $errorLogs = $query->latest()->paginate(10);

        return view('errorlogs.index', compact('errorLogs'));
    }

    /**
     * Clear (delete) all error log entries from storage.
     *
     *  This action is irreversible; use middleware to restrict access.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        ErrorLog::truncate();

        return redirect()->route('error-logs.index')->with('success', 'All error logs have been cleared.');
    }
}
