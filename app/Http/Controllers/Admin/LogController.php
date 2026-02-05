<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display activity logs
     */
    public function index(Request $request)
    {
        $query = Log::with(['user', 'pengaduan']);

        // Filter by pengaduan
        if ($request->filled('pengaduan_id')) {
            $query->where('pengaduan_id', $request->pengaduan_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->latest()->paginate(50);

        // Get unique actions for filters
        $actions = Log::distinct()->pluck('action');

        return view('admin.logs.index', compact('logs', 'actions'));
    }
}
