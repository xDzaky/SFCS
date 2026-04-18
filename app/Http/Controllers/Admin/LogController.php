<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $errorStats = [
            'failed_jobs_24h' => DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count(),
            'failed_jobs_7d' => DB::table('failed_jobs')->where('failed_at', '>=', now()->subDays(7))->count(),
            'activity_24h' => Log::where('created_at', '>=', now()->subDay())->count(),
        ];

        return view('admin.logs.index', compact('logs', 'actions', 'errorStats'));
    }

    /**
     * Export filtered activity logs to CSV.
     */
    public function export(Request $request)
    {
        $query = Log::with(['user', 'pengaduan']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        $logs = $query->latest()->limit(5000)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-logs-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($logs): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Waktu', 'User', 'Role', 'Aksi', 'Kode Pengaduan', 'Deskripsi', 'IP']);

            foreach ($logs as $log) {
                fputcsv($stream, [
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $log->user->name ?? '-',
                    $log->user->role ?? '-',
                    $log->action,
                    $log->pengaduan->kode_pengaduan ?? '-',
                    $log->description,
                    $log->ip_address,
                ]);
            }

            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }
}
