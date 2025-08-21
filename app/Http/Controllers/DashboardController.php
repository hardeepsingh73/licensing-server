<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApiLog;
use App\Models\EmailLog;
use App\Models\ErrorLog;
use App\Models\LoginHistory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $totalActivity = ActivityLog::count();
        $emails = EmailLog::count();
        $errors = ErrorLog::count();
        $apiCalls = ApiLog::count();
        $loginHistory = LoginHistory::count();

        return view('dashboard', compact('totalActivity', 'emails', 'errors', 'apiCalls', 'loginHistory'));
    }
}
