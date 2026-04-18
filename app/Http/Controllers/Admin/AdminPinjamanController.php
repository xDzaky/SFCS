<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Notification;
use App\Models\Pinjaman;
use App\Models\PinjamanLog;
use App\Services\PinjamanAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPinjamanController extends Controller
{
    public function __construct(private readonly PinjamanAvailabilityService $availabilityService)
    {
    }

    public function index(Request $request)
    {
        $query = Pinjaman::query()->with(['user', 'barang'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode_pinjaman', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('barang', fn ($bq) => $bq->where('nama', 'like', "%{$search}%"));
            });
        }

        $pinjamans = $query->paginate(15);

        return view('admin.pinjaman.index', compact('pinjamans'));
    }

    public function show(Pinjaman $pinjaman)
    {
        $pinjaman->load(['user', 'barang', 'logs.user', 'feedback']);

        return view('admin.pinjaman.show', compact('pinjaman'));
    }

    public function approve(Request $request, Pinjaman $pinjaman)
    {
        if ($pinjaman->status !== Pinjaman::STATUS_PENDING) {
            return back()->with('error', 'Status pinjaman tidak valid untuk disetujui.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($pinjaman, $validated): void {
            $pinjaman->update([
                'status' => Pinjaman::STATUS_DISETUJUI,
                'approved_at' => now(),
                'catatan_admin' => $validated['catatan_admin'] ?? null,
            ]);

            PinjamanLog::createLog($pinjaman->id, Auth::id(), 'approve', 'Pengajuan pinjaman disetujui admin.');

            Notification::send(
                $pinjaman->user_id,
                Notification::JENIS_PINJAMAN_STATUS,
                'Pinjaman Disetujui',
                "Pengajuan {$pinjaman->kode_pinjaman} disetujui admin.",
                route('pinjaman.show', $pinjaman)
            );
        });

        return back()->with('success', 'Pinjaman berhasil disetujui.');
    }

    public function reject(Request $request, Pinjaman $pinjaman)
    {
        if ($pinjaman->status !== Pinjaman::STATUS_PENDING) {
            return back()->with('error', 'Status pinjaman tidak valid untuk ditolak.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'required|string|max:1000',
        ]);

        $pinjaman->update([
            'status' => Pinjaman::STATUS_DITOLAK,
            'catatan_admin' => $validated['catatan_admin'],
        ]);

        PinjamanLog::createLog($pinjaman->id, Auth::id(), 'reject', 'Pengajuan pinjaman ditolak admin.');

        Notification::send(
            $pinjaman->user_id,
            Notification::JENIS_PINJAMAN_STATUS,
            'Pinjaman Ditolak',
            "Pengajuan {$pinjaman->kode_pinjaman} ditolak admin. Alasan: {$validated['catatan_admin']}",
            route('pinjaman.show', $pinjaman)
        );

        return back()->with('success', 'Pinjaman berhasil ditolak.');
    }

    public function checkOut(Request $request, Pinjaman $pinjaman)
    {
        if ($pinjaman->status !== Pinjaman::STATUS_DISETUJUI) {
            return back()->with('error', 'Hanya pinjaman disetujui yang bisa check-out.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($pinjaman, $validated): void {
            $barang = Barang::query()->lockForUpdate()->findOrFail($pinjaman->barang_id);
            $this->availabilityService->reserveStock($barang, $pinjaman->qty);

            $pinjaman->update([
                'status' => Pinjaman::STATUS_DIPINJAM,
                'checked_out_at' => now(),
                'catatan_admin' => $validated['catatan_admin'] ?? $pinjaman->catatan_admin,
            ]);

            PinjamanLog::createLog($pinjaman->id, Auth::id(), 'checkout', 'Barang dipinjamkan ke peminjam.');

            Notification::send(
                $pinjaman->user_id,
                Notification::JENIS_PINJAMAN_STATUS,
                'Barang Sudah Diserahkan',
                "Barang untuk {$pinjaman->kode_pinjaman} sudah dapat digunakan.",
                route('pinjaman.show', $pinjaman)
            );
        });

        return back()->with('success', 'Check-out berhasil.');
    }

    public function checkIn(Request $request, Pinjaman $pinjaman)
    {
        if (!in_array($pinjaman->status, [Pinjaman::STATUS_DIPINJAM, Pinjaman::STATUS_TERLAMBAT], true)) {
            return back()->with('error', 'Hanya pinjaman aktif/terlambat yang bisa check-in.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($pinjaman, $validated): void {
            $barang = Barang::query()->lockForUpdate()->findOrFail($pinjaman->barang_id);
            $this->availabilityService->releaseStock($barang, $pinjaman->qty);

            $pinjaman->update([
                'status' => Pinjaman::STATUS_SELESAI,
                'tgl_kembali' => now(),
                'catatan_admin' => $validated['catatan_admin'] ?? $pinjaman->catatan_admin,
            ]);

            PinjamanLog::createLog($pinjaman->id, Auth::id(), 'checkin', 'Barang sudah dikembalikan peminjam.');

            Notification::send(
                $pinjaman->user_id,
                Notification::JENIS_PINJAMAN_STATUS,
                'Pinjaman Selesai',
                "Pinjaman {$pinjaman->kode_pinjaman} sudah ditutup. Mohon beri feedback.",
                route('pinjaman.show', $pinjaman)
            );
        });

        return back()->with('success', 'Check-in berhasil dan pinjaman ditutup.');
    }

    public function forceClose(Request $request, Pinjaman $pinjaman)
    {
        if (in_array($pinjaman->status, [Pinjaman::STATUS_SELESAI, Pinjaman::STATUS_DITOLAK], true)) {
            return back()->with('error', 'Pinjaman sudah final.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($pinjaman, $validated): void {
            if (in_array($pinjaman->status, [Pinjaman::STATUS_DIPINJAM, Pinjaman::STATUS_TERLAMBAT], true)) {
                $barang = Barang::query()->lockForUpdate()->findOrFail($pinjaman->barang_id);
                $this->availabilityService->releaseStock($barang, $pinjaman->qty);
            }

            $pinjaman->update([
                'status' => Pinjaman::STATUS_SELESAI,
                'tgl_kembali' => now(),
                'catatan_admin' => $validated['catatan_admin'],
            ]);

            PinjamanLog::createLog($pinjaman->id, Auth::id(), 'force_close', 'Pinjaman ditutup paksa admin.');
        });

        return back()->with('success', 'Pinjaman ditutup paksa oleh admin.');
    }

    public function adjustStock(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'stok_total' => 'required|integer|min:0',
            'stok_tersedia' => 'required|integer|min:0',
            'stok_rusak' => 'required|integer|min:0',
        ]);

        if ($validated['stok_tersedia'] + $validated['stok_rusak'] > $validated['stok_total']) {
            return back()->with('error', 'Stok tersedia + rusak tidak boleh melebihi stok total.');
        }

        $barang->update($validated);

        return back()->with('success', 'Stok barang berhasil diperbarui.');
    }

    public function export(Request $request)
    {
        $query = Pinjaman::query()->with(['user:id,name', 'barang:id,nama']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rows = $query->latest()->get();

        $csv = [
            ['Kode', 'Peminjam', 'Barang', 'Qty', 'Status', 'Pinjam', 'Jatuh Tempo', 'Kembali'],
        ];

        foreach ($rows as $row) {
            $csv[] = [
                $row->kode_pinjaman,
                $row->user->name ?? '-',
                $row->barang->nama ?? '-',
                $row->qty,
                $row->status,
                optional($row->tgl_pinjam)->format('Y-m-d H:i'),
                optional($row->tgl_jatuh_tempo)->format('Y-m-d H:i'),
                optional($row->tgl_kembali)->format('Y-m-d H:i'),
            ];
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($csv as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-pinjaman-'.now()->format('Ymd-His').'.csv"',
        ]);
    }
}
