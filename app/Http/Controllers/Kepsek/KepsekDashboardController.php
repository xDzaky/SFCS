<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Models\Kategori;
use App\Models\Gedung;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KepsekDashboardController extends Controller
{
    /**
     * Display kepsek dashboard
     */
    public function index()
    {
        // High level stats
        $avgRating = Feedback::selectRaw('AVG((rating_respon + rating_kualitas + rating_pelayanan) / 3) as avg_rating')
            ->value('avg_rating');
            
        $stats = [
            'total_bulan_ini' => Pengaduan::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'selesai_bulan_ini' => Pengaduan::where('status', 'selesai')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count(),
            'rata_rating' => round($avgRating ?? 0, 1),
            'pending' => Pengaduan::where('status', 'pending')->count(),
            'overdue' => Pengaduan::whereNotIn('status', ['selesai', 'ditolak'])
                ->whereRaw('DATEDIFF(NOW(), created_at) > CASE prioritas 
                    WHEN "urgent" THEN 1
                    WHEN "tinggi" THEN 2 
                    WHEN "sedang" THEN 5 
                    ELSE 7 END')
                ->count(),
        ];

        // Completion rate
        $totalPengaduan = Pengaduan::count();
        $selesai = Pengaduan::where('status', 'selesai')->count();
        $stats['completion_rate'] = $totalPengaduan > 0 
            ? round(($selesai / $totalPengaduan) * 100) 
            : 0;

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

        // Recent high priority issues
        $highPriorityPengaduans = Pengaduan::with(['kategori', 'gedung', 'ruangan.gedung', 'user', 'teknisi'])
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
                $avgRating = Feedback::whereHas('pengaduan', function ($q) use ($teknisi) {
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

        return view('dashboard.kepsek', compact(
            'stats',
            'kategoriPerformance',
            'monthlyTrend',
            'highPriorityPengaduans',
            'teknisiPerformance'
        ));
    }

    /**
     * Display reports page
     */
    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Summary stats for period
        $periodStats = [
            'total' => Pengaduan::whereBetween('created_at', [$startDate, $endDate])->count(),
            'selesai' => Pengaduan::where('status', 'selesai')
                ->whereBetween('updated_at', [$startDate, $endDate])->count(),
            'ditolak' => Pengaduan::where('status', 'ditolak')
                ->whereBetween('updated_at', [$startDate, $endDate])->count(),
        ];

        // By gedung
        $byGedung = Gedung::with(['ruangans' => function ($query) use ($startDate, $endDate) {
            $query->withCount(['pengaduans' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }]);
        }])->get()->map(function ($gedung) {
            return [
                'nama' => $gedung->nama,
                'total' => $gedung->ruangans->sum('pengaduans_count'),
            ];
        })->sortByDesc('total');

        // By kategori
        $byKategori = Kategori::withCount(['pengaduans' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->orderByDesc('pengaduans_count')->get();

        // Average resolution time
        $avgResolutionTime = Pengaduan::where('status', 'selesai')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');

        return view('dashboard.kepsek-reports', compact(
            'startDate',
            'endDate',
            'periodStats',
            'byGedung',
            'byKategori',
            'avgResolutionTime'
        ));
    }
}
