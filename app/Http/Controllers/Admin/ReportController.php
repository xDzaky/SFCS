<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Models\Kategori;
use App\Models\Gedung;
use App\Models\User;
use App\Models\Feedback;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Overview stats
        $stats = [
            'total_pengaduan' => Pengaduan::count(),
            'total_selesai' => Pengaduan::where('status', 'selesai')->count(),
            'total_pending' => Pengaduan::where('status', 'pending')->count(),
            'avg_rating' => round(Feedback::selectRaw('AVG((rating_respon + rating_kualitas + rating_pelayanan) / 3) as avg_rating')->value('avg_rating') ?? 0, 1),
            'total_users' => User::where('is_active', true)->count(),
        ];

        // Completion rate
        $stats['completion_rate'] = $stats['total_pengaduan'] > 0
            ? round(($stats['total_selesai'] / $stats['total_pengaduan']) * 100)
            : 0;

        return view('admin.reports.index', compact('stats'));
    }

    /**
     * Pengaduan reports
     */
    public function pengaduan(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Stats for period
        $periodStats = Pengaduan::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = "ditolak" THEN 1 ELSE 0 END) as ditolak,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "diverifikasi" THEN 1 ELSE 0 END) as diverifikasi,
                SUM(CASE WHEN status = "diproses" THEN 1 ELSE 0 END) as diproses
            ')
            ->first();

        // By status
        $byStatus = Pengaduan::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        // By prioritas
        $byPrioritas = Pengaduan::whereBetween('created_at', [$startDate, $endDate])
            ->select('prioritas', DB::raw('COUNT(*) as total'))
            ->groupBy('prioritas')
            ->get();

        // By kategori
        $byKategori = Kategori::withCount(['pengaduans' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->orderByDesc('pengaduans_count')->get();

        // By gedung
        $byGedung = Gedung::withCount(['pengaduans' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get()->map(function ($gedung) {
            return [
                'id' => $gedung->id,
                'nama' => $gedung->nama,
                'total' => $gedung->pengaduans_count ?? 0,
            ];
        })->sortByDesc('total');

        // Daily trend
        $dailyTrend = Pengaduan::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Average resolution time by prioritas
        $avgResolutionTime = Pengaduan::where('status', 'selesai')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select(
                'prioritas',
                DB::raw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            )
            ->groupBy('prioritas')
            ->get();

        return view('admin.reports.pengaduan', compact(
            'startDate',
            'endDate',
            'periodStats',
            'byStatus',
            'byPrioritas',
            'byKategori',
            'byGedung',
            'dailyTrend',
            'avgResolutionTime'
        ));
    }

    /**
     * Performance reports
     */
    public function performance(Request $request)
    {
        $startDate = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('date_to', now()->format('Y-m-d'));
        $teknisiId = $request->get('teknisi_id');

        // Teknisi performance
        $teknisiQuery = User::where('role', 'teknisi');
        
        if ($teknisiId) {
            $teknisiQuery->where('id', $teknisiId);
        }
        
        $teknisiPerformance = $teknisiQuery
            ->with(['assignedPengaduans' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($teknisi) use ($startDate, $endDate) {
                $pengaduans = $teknisi->assignedPengaduans;
                $selesai = $pengaduans->where('status', 'selesai');
                $dikerjakan = $pengaduans->whereIn('status', ['diverifikasi', 'diproses']);
                
                $avgRating = Feedback::whereHas('pengaduan', function ($q) use ($teknisi, $startDate, $endDate) {
                    $q->where('teknisi_id', $teknisi->id)
                        ->whereBetween('created_at', [$startDate, $endDate]);
                })->selectRaw('AVG((rating_respon + rating_kualitas + rating_pelayanan) / 3) as avg_rating')->value('avg_rating');

                $avgResolutionTime = $selesai->avg(function ($p) {
                    return $p->created_at->diffInHours($p->updated_at);
                });

                // Calculate SLA compliance
                $slaCompliant = $selesai->filter(function ($p) {
                    $slaLimit = match($p->prioritas) {
                        'urgent' => 1,
                        'tinggi' => 2,
                        'sedang' => 5,
                        default => 7,
                    };
                    return $p->created_at->diffInDays($p->updated_at) <= $slaLimit;
                })->count();
                
                $slaPercentage = $selesai->count() > 0 
                    ? round(($slaCompliant / $selesai->count()) * 100)
                    : 0;

                return [
                    'id' => $teknisi->id,
                    'name' => $teknisi->name,
                    'email' => $teknisi->email,
                    'ditugaskan' => $pengaduans->count(),
                    'dikerjakan' => $dikerjakan->count(),
                    'selesai' => $selesai->count(),
                    'completion_rate' => $pengaduans->count() > 0
                        ? round(($selesai->count() / $pengaduans->count()) * 100)
                        : 0,
                    'rating' => round($avgRating ?? 0, 1),
                    'avg_time' => round($avgResolutionTime ?? 0, 1),
                    'sla_percentage' => $slaPercentage,
                ];
            })
            ->sortByDesc('selesai');

        // SLA compliance
        $slaCompliance = Pengaduan::where('status', 'selesai')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get()
            ->groupBy('prioritas')
            ->map(function ($group, $prioritas) {
                $slaLimit = match($prioritas) {
                    'urgent' => 1,
                    'tinggi' => 2,
                    'sedang' => 5,
                    default => 7,
                };

                $total = $group->count();
                $onTime = $group->filter(function ($p) use ($slaLimit) {
                    return $p->created_at->diffInDays($p->updated_at) <= $slaLimit;
                })->count();

                return [
                    'prioritas' => $prioritas,
                    'total' => $total,
                    'on_time' => $onTime,
                    'compliance_rate' => $total > 0 ? round(($onTime / $total) * 100) : 0,
                ];
            });

        // Rating distribution
        $ratingDistribution = Feedback::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('ROUND((rating_respon + rating_kualitas + rating_pelayanan) / 3) as rating, COUNT(*) as total')
            ->groupBy(DB::raw('ROUND((rating_respon + rating_kualitas + rating_pelayanan) / 3)'))
            ->orderBy('rating')
            ->get()
            ->pluck('total', 'rating')
            ->toArray();
        
        // Ensure all ratings 1-5 are present
        $ratingDistribution = array_replace([1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0], $ratingDistribution);

        // Get all teknisi for filter dropdown
        $teknisis = User::where('role', 'teknisi')->orderBy('name')->get();

        // Calculate summary statistics
        $allPengaduans = Pengaduan::whereBetween('created_at', [$startDate, $endDate]);
        if ($teknisiId) {
            $allPengaduans->where('teknisi_id', $teknisiId);
        }
        $allPengaduans = $allPengaduans->get();
        
        $selesaiPengaduans = $allPengaduans->where('status', 'selesai');
        
        $avgResolutionTime = $selesaiPengaduans->avg(function ($p) {
            return $p->created_at->diffInHours($p->updated_at);
        });
        
        $avgRating = Feedback::whereHas('pengaduan', function ($q) use ($startDate, $endDate, $teknisiId) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
            if ($teknisiId) {
                $q->where('teknisi_id', $teknisiId);
            }
        })->selectRaw('AVG((rating_respon + rating_kualitas + rating_pelayanan) / 3) as avg_rating')->value('avg_rating');
        
        $slaCompliantCount = $selesaiPengaduans->filter(function ($p) {
            $slaLimit = match($p->prioritas) {
                'urgent' => 1,
                'tinggi' => 2,
                'sedang' => 5,
                default => 7,
            };
            return $p->created_at->diffInDays($p->updated_at) <= $slaLimit;
        })->count();
        
        $summary = [
            'total_selesai' => $selesaiPengaduans->count(),
            'avg_resolution_time' => round($avgResolutionTime ?? 0, 1),
            'avg_rating' => round($avgRating ?? 0, 1),
            'sla_compliance' => $selesaiPengaduans->count() > 0 
                ? round(($slaCompliantCount / $selesaiPengaduans->count()) * 100)
                : 0,
        ];

        return view('admin.reports.performance', compact(
            'startDate',
            'endDate',
            'teknisiPerformance',
            'slaCompliance',
            'ratingDistribution',
            'teknisis',
            'summary'
        ));
    }

    /**
     * Export report
     */
    public function export(Request $request, $type)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));


        $filename = "laporan-{$type}-{$startDate}-{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($type, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            if ($type === 'pengaduan') {
                $this->exportPengaduan($file, $startDate, $endDate);
            } elseif ($type === 'performance') {
                $this->exportPerformance($file, $startDate, $endDate);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPengaduan($file, $startDate, $endDate)
    {
        fputcsv($file, [
            'Kode',
            'Judul',
            'Kategori',
            'Gedung',
            'Lokasi Detail',
            'Pelapor',
            'Prioritas',
            'Status',
            'Teknisi',
            'Tanggal Dibuat',
            'Tanggal Selesai',
            'Durasi (Hari)',
        ]);

        $pengaduans = Pengaduan::with(['kategori', 'gedung', 'user', 'teknisi'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        foreach ($pengaduans as $p) {
            $durasi = $p->status === 'selesai'
                ? $p->created_at->diffInDays($p->updated_at)
                : '-';

            fputcsv($file, [
                $p->kode_pengaduan ?? $p->id,
                $p->judul,
                $p->kategori->nama ?? '-',
                $p->gedung->nama ?? '-',
                $p->lokasi_detail ?? '-',
                $p->user->name ?? '-',
                ucfirst($p->prioritas),
                ucfirst($p->status),
                $p->teknisi->name ?? '-',
                $p->created_at->format('Y-m-d H:i'),
                $p->status === 'selesai' ? $p->updated_at->format('Y-m-d H:i') : '-',
                $durasi,
            ]);
        }
    }

    private function exportPerformance($file, $startDate, $endDate)
    {
        fputcsv($file, [
            'Teknisi',
            'Total Ditugaskan',
            'Selesai',
            'Dalam Proses',
            'Completion Rate (%)',
            'Rata-rata Rating',
            'Rata-rata Durasi (Hari)',
        ]);

        $teknisis = User::where('role', 'teknisi')->get();

        foreach ($teknisis as $teknisi) {
            $pengaduans = Pengaduan::where('teknisi_id', $teknisi->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $selesai = $pengaduans->where('status', 'selesai');

            $avgRating = Feedback::whereHas('pengaduan', function ($q) use ($teknisi, $startDate, $endDate) {
                $q->where('teknisi_id', $teknisi->id)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            })->selectRaw('AVG((rating_respon + rating_kualitas + rating_pelayanan) / 3) as avg_rating')->value('avg_rating');

            $avgDays = $selesai->avg(function ($p) {
                return $p->created_at->diffInDays($p->updated_at);
            });

            fputcsv($file, [
                $teknisi->name,
                $pengaduans->count(),
                $selesai->count(),
                $pengaduans->whereIn('status', ['diverifikasi', 'diproses'])->count(),
                $pengaduans->count() > 0 ? round(($selesai->count() / $pengaduans->count()) * 100) : 0,
                round($avgRating ?? 0, 1),
                round($avgDays ?? 0, 1),
            ]);
        }
    }
}
