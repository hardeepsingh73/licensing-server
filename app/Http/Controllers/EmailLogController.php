<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Services\SearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmailLogController extends Controller implements HasMiddleware
{
    // SearchService instance for handling search functionality
    protected SearchService $searchService;

    /**
     * Constructor to inject the SearchService dependency
     *
     * @param SearchService $searchService
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Define middleware for controller methods
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            // Only users with permission "view email logs" can view the index page
            new Middleware('permission:view email logs', only: ['index']),
            // Only users with permission "clear email logs" can clear logs
            new Middleware('permission:clear email logs', only: ['clear']),
        ];
    }

    /**
     * Display a paginated list of email logs with search functionality
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Use SearchService for keyword-based search on 'to', 'subject', and 'status' fields
        $query = $this->searchService->search(
            EmailLog::query(),
            ['to', 'subject', 'status'],
            $request
        );

        // Paginate logs - latest first, 10 per page
        $emailLogs = $query->latest()->paginate(10);

        // Return the view with paginated email logs
        return view('emaillogs.index', compact('emailLogs'));
    }

    /**
     * Clear all email logs from the database
     *
     * @return RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        // Truncate the emaillogs table
        EmailLog::truncate();

        // Redirect back to index with success message
        return redirect()->route('email-logs.index')->with('success', 'All email logs have been cleared.');
    }

    /**
     * Display details of a specific email log
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        // Find the email log or fail with 404
        $log = EmailLog::findOrFail($id);

        // Return the view with the email log details
        return view('emaillogs.view', [
            'emailLog' => $log
        ]);
    }
}
