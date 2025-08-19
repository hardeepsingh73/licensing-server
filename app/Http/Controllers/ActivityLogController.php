<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ActivityLogController extends Controller implements HasMiddleware
{
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
            // Only users with permission "view activity logs" can view the index page
            new Middleware('permission:view activity logs', only: ['index']),
            // Only users with permission "clear activity logs" can clear logs
            new Middleware('permission:clear activity logs', only: ['clear']),
        ];
    }
    /**
     * Display a paginated list of activity logs with optional filters.
     *
     * @param  \Illuminate\Http\Request  $request
     *        Optional query params:
     *        - user   : Filter by causer_id
     *        - type   : Filter by subject_type
     *        - event  : Filter by event name
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['subject', 'causer'])->latest();

        // Apply filters if provided
        if ($request->filled('user')) {
            $query->where('causer_id', $request->user);
        }

        if ($request->filled('type')) {
            $query->where('subject_type', $request->type);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Paginate results
        $logs = $query->paginate(10);

        return view('activity-logs.index', compact('logs'));
    }

    /**
     * Show details of a specific activity log.
     *
     * Route Model Binding automatically fetches the ActivityLog instance.
     *
     * @param  \App\Models\ActivityLog  $activityLog
     * @return \Illuminate\View\View
     */
    public function show(ActivityLog $activityLog)
    {
        return view('activity-logs.view', compact('activityLog'));
    }

    /**
     * Export filtered activity logs as a CSV file download.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        // Build the query with optional filters
        $logs = ActivityLog::query()
            ->when($request->filled('user'), fn($q) => $q->where('causer_id', $request->user))
            ->when($request->filled('type'), fn($q) => $q->where('subject_type', $request->type))
            ->when($request->filled('event'), fn($q) => $q->where('event', $request->event))
            ->latest()
            ->get();

        // Set CSV filename with timestamp
        $fileName = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Define HTTP headers for file download
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        // Stream CSV output to the browser
        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV column headers
            fputcsv($file, [
                'ID',
                'Event',
                'Description',
                'Subject Type',
                'Subject ID',
                'Causer Type',
                'Causer ID',
                'IP Address',
                'User Agent',
                'URL',
                'Method',
                'Created At'
            ]);

            // Write log data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->event,
                    $log->description,
                    $log->subject_type,
                    $log->subject_id,
                    $log->causer_type,
                    $log->causer_id,
                    $log->ip_address,
                    $log->user_agent,
                    $log->url,
                    $log->method,
                    $log->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear all activity logs from the database.
     *
     *  Use with caution! This action is irreversible.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        ActivityLog::truncate();

        return redirect()
            ->route('activity-logs.index')
            ->with('success', 'All activity logs have been cleared.');
    }
    
    public function restore($id)
    {
        $activityLog = ActivityLog::findOrFail($id);

        $modelClass = $activityLog->subject_type;
        $modelId    = $activityLog->subject_id;

        if (!class_exists($modelClass)) {
            return redirect()->back()->with('error', 'Model class does not exist.');
        }

        // Handle Deleted Event
        if ($activityLog->event === ActivityLog::EVENT_DELETED) {
            if (!in_array(SoftDeletes::class, class_uses_recursive($modelClass))) {
                return redirect()->back()->with('error', 'This model does not support restoring.');
            }

            $model = $modelClass::withTrashed()->find($modelId);
            if (!$model || !$model->trashed()) {
                return redirect()->back()->with('error', 'Record not found or already active.');
            }

            $model->restore();
            return redirect()->back()->with('success', class_basename($modelClass) . ' restored successfully.');
        }

        // Handle Updated Event - revert to old properties
        if ($activityLog->event === ActivityLog::EVENT_UPDATED) {
            $model = $modelClass::find($modelId);
            if (!$model) {
                return redirect()->back()->with('error', 'Record not found.');
            }

            $oldValues = $activityLog->properties['old'] ?? null;
            if (!$oldValues) {
                return redirect()->back()->with('error', 'No old values found to revert.');
            }

            $model->update($oldValues);
            return redirect()->back()->with('success', class_basename($modelClass) . ' reverted to previous values.');
        }

        return redirect()->back()->with('error', 'This action is only available for deleted or updated events.');
    }
}
