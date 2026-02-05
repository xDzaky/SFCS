<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\User;
use App\Models\Gedung;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Route to appropriate dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'superadmin' => $this->superadminDashboard(),
            'admin' => $this->adminDashboard(),
            'kepsek' => $this->kepsekDashboard(),
            'teknisi' => $this->teknisiDashboard(),
            default => $this->siswaDashboard(),
        };
    }

    /**
     * Siswa/Guru Dashboard
     */
    private function siswaDashboard()
    {
        $user = Auth::user();

        $pengaduans = Pengaduan::with(['kategori', 'ruangan.gedung', 'photos'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total' => Pengaduan::where('user_id', $user->id)->count(),
            'pending' => Pengaduan::where('user_id', $user->id)->pending()->count(),
            'proses' => Pengaduan::where('user_id', $user->id)->proses()->count(),
            'selesai' => Pengaduan::where('user_id', $user->id)->selesai()->count(),
        ];

        return view('dashboard.siswa', compact('pengaduans', 'stats'));
    }

    /**
     * Teknisi Dashboard
     */
    private function teknisiDashboard()
    {
        $user = Auth::user();

        // Pengaduan assigned to this teknisi
        $assignedPengaduans = Pengaduan::with(['kategori', 'ruangan.gedung', 'user'])
            ->where('teknisi_id', $user->id)
            ->whereNotIn('status', ['selesai', 'ditolak'])
            ->latest()
            ->get();

        // Urgent pengaduans
        $urgentPengaduans = Pengaduan::with(['kategori', 'ruangan.gedung', 'user'])
            ->where('teknisi_id', $user->id)
            ->whereIn('prioritas', ['urgent', 'tinggi'])
            ->whereNotIn('status', ['selesai', 'ditolak'])
            ->count();

        $stats = [
            'assigned' => $assignedPengaduans->count(),
            'urgent' => $urgentPengaduans,
            'selesai_bulan_ini' => Pengaduan::where('teknisi_id', $user->id)
                ->where('status', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->count(),
        ];

        return view('dashboard.teknisi', compact('assignedPengaduans', 'stats'));
    }

    /**
     * Admin Dashboard
     */
    private function adminDashboard()
    {
        // Overall stats
        $stats = [
            'total' => Pengaduan::count(),
            'pending' => Pengaduan::pending()->count(),
            'proses' => Pengaduan::proses()->count(),
            'selesai' => Pengaduan::selesai()->count(),
            'overdue' => Pengaduan::whereNotIn('status', ['selesai', 'ditolak'])
                ->whereRaw('DATEDIFF(NOW(), created_at) > CASE prioritas 
                    WHEN "tinggi" THEN 1 
                    WHEN "sedang" THEN 3 
                    ELSE 7 END')
                ->count(),
        ];

        // Recent pengaduans
        $recentPengaduans = Pengaduan::with(['kategori', 'ruangan.gedung', 'user', 'teknisi'])
            ->latest()
            ->take(10)
            ->get();

        // Pengaduan by kategori for chart
        $byKategori = Kategori::withCount('pengaduans')
            ->orderByDesc('pengaduans_count')
            ->take(5)
            ->get();

        // Pengaduan by gedung for chart
        $byGedung = Gedung::withCount('pengaduans')
            ->orderByDesc('pengaduans_count')
            ->take(5)
            ->get()
            ->map(function ($gedung) {
                return [
                    'nama' => $gedung->nama,
                    'total' => $gedung->pengaduans_count,
                ];
            });

        // Monthly trend (last 6 months)
        $monthlyTrend = Pengaduan::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Teknisi list
        $teknisis = User::where('role', 'teknisi')
            ->where('is_active', true)
            ->withCount(['assignedPengaduans' => function ($query) {
                $query->whereNotIn('status', ['selesai', 'ditolak']);
            }])
            ->get();

        return view('dashboard.admin', compact(
            'stats',
            'recentPengaduans',
            'byKategori',
            'byGedung',
            'monthlyTrend',
            'teknisis'
        ));
    }

    /**
     * Kepsek Dashboard
     */
    private function kepsekDashboard()
    {
        // High level stats
        $avgRating = \App\Models\Feedback::selectRaw('AVG((rating_respon + rating_kualitas + rating_pelayanan) / 3) as avg_rating')
            ->value('avg_rating');
            
        $stats = [
            'total_bulan_ini' => Pengaduan::whereMonth('created_at', now()->month)->count(),
            'selesai_bulan_ini' => Pengaduan::selesai()->whereMonth('updated_at', now()->month)->count(),
            'rata_rating' => round($avgRating ?? 0, 1),
            'pending' => Pengaduan::pending()->count(),
        ];

        // Performance by kategori
        $kategoriPerformance = Kategori::withCount([
            'pengaduans',
            'pengaduans as selesai_count' => function ($query) {
                $query->where('status', 'selesai');
            }
        ])->get()->map(function ($kategori) {
            return [
                'nama' => $kategori->nama,
                'total' => $kategori->pengaduans_count,
                'selesai' => $kategori->selesai_count,
                'persentase' => $kategori->pengaduans_count > 0
                    ? round(($kategori->selesai_count / $kategori->pengaduans_count) * 100)
                    : 0,
            ];
        })->sortByDesc('total');

        // Recent high priority issues
        $highPriorityPengaduans = Pengaduan::with(['kategori', 'ruangan.gedung', 'user', 'assignedTo'])
            ->whereIn('prioritas', ['urgent', 'tinggi'])
            ->whereNotIn('status', ['selesai', 'ditolak'])
            ->latest()
            ->take(5)
            ->get();

        // Teknisi performance
        $teknisiPerformance = User::where('role', 'teknisi')
            ->where('is_active', true)
            ->withCount([
                'assignedPengaduans as total_ditangani',
                'assignedPengaduans as selesai_count' => function ($query) {
                    $query->where('status', 'selesai');
                }
            ])
            ->get()
            ->map(function ($teknisi) {
                $avgRating = \App\Models\Feedback::whereHas('pengaduan', function ($q) use ($teknisi) {
                    $q->where('teknisi_id', $teknisi->id);
                })->selectRaw('AVG((rating_respon + rating_kualitas + rating_pelayanan) / 3) as avg_rating')
                ->value('avg_rating');

                return [
                    'nama' => $teknisi->name,
                    'total' => $teknisi->total_ditangani,
                    'selesai' => $teknisi->selesai_count,
                    'rating' => round($avgRating ?? 0, 1),
                ];
            })
            ->sortByDesc('selesai');

        // Monthly trend (last 6 months)
        $monthlyTrend = Pengaduan::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('dashboard.kepsek', compact('stats', 'kategoriPerformance', 'highPriorityPengaduans', 'teknisiPerformance', 'monthlyTrend'));
    }

    /**
     * Superadmin Dashboard
     */
    private function superadminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_pengaduan' => Pengaduan::count(),
            'pending' => Pengaduan::pending()->count(),
            'total_gedung' => Gedung::count(),
            'total_kategori' => Kategori::count(),
        ];

        // Users by role
        $usersByRole = User::select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->get();

        // System activity (recent logs)
        $recentLogs = \App\Models\Log::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.superadmin', compact('stats', 'usersByRole', 'recentLogs'));
    }
}
