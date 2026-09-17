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

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('unit_sarpras')) {
            $query->whereHas('barang', fn($q) => $q->where('unit_sarpras', $request->unit_sarpras));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode_pinjaman', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('barang', fn($bq) => $bq->where('nama', 'like', "%{$search}%"));
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
            return back()->with('error', 'Status pengajuan tidak valid untuk disetujui.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($pinjaman, $validated): void {
            $isPermintaan = $pinjaman->tipe === Pinjaman::TIPE_MINTA;

            if ($isPermintaan) {
                // Permintaan: langsung kurangi stok saat disetujui, lalu selesai
                $barang = Barang::query()->lockForUpdate()->findOrFail($pinjaman->barang_id);
                $this->availabilityService->reserveStock($barang, $pinjaman->qty);

                $pinjaman->update([
                    'status'        => Pinjaman::STATUS_SELESAI,
                    'approved_at'   => now(),
                    'checked_out_at' => now(),
                    'tgl_kembali'   => now(),
                    'catatan_admin' => $validated['catatan_admin'] ?? null,
                ]);

                PinjamanLog::createLog($pinjaman->id, Auth::id(), 'approve', 'Permintaan barang disetujui dan barang diserahkan.');

                Notification::send(
                    $pinjaman->user_id,
                    Notification::JENIS_PINJAMAN_STATUS,
                    'Permintaan Barang Disetujui',
                    "Permintaan {$pinjaman->kode_pinjaman} disetujui. Silakan ambil barang di Sarpras Bawah.",
                    route('pinjaman.show', $pinjaman)
                );
            } else {
                // Pinjaman biasa: tunggu checkout
                $pinjaman->update([
                    'status'        => Pinjaman::STATUS_DISETUJUI,
                    'approved_at'   => now(),
                    'catatan_admin' => $validated['catatan_admin'] ?? null,
                ]);

                PinjamanLog::createLog($pinjaman->id, Auth::id(), 'approve', 'Pinjaman disetujui admin.');

                Notification::send(
                    $pinjaman->user_id,
                    Notification::JENIS_PINJAMAN_STATUS,
                    'Pinjaman Disetujui',
                    "Pengajuan {$pinjaman->kode_pinjaman} disetujui. Silakan ambil barang di Sarpras Atas.",
                    route('pinjaman.show', $pinjaman)
                );
            }
        });

        return back()->with('success', $pinjaman->tipe === 'minta'
            ? 'Permintaan barang disetujui dan stok dikurangi.'
            : 'Pinjaman berhasil disetujui.'
        );
    }

    public function reject(Request $request, Pinjaman $pinjaman)
    {
        if ($pinjaman->status !== Pinjaman::STATUS_PENDING) {
            return back()->with('error', 'Status pengajuan tidak valid untuk ditolak.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'required|string|max:1000',
        ]);

        $pinjaman->update([
            'status'        => Pinjaman::STATUS_DITOLAK,
            'catatan_admin' => $validated['catatan_admin'],
        ]);

        PinjamanLog::createLog($pinjaman->id, Auth::id(), 'reject', 'Pengajuan ditolak admin.');

        Notification::send(
            $pinjaman->user_id,
            Notification::JENIS_PINJAMAN_STATUS,
            'Pengajuan Ditolak',
            "Pengajuan {$pinjaman->kode_pinjaman} ditolak. Alasan: {$validated['catatan_admin']}",
            route('pinjaman.show', $pinjaman)
        );

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    public function checkOut(Request $request, Pinjaman $pinjaman)
    {
        // Permintaan (minta) tidak memiliki tahap checkout — langsung selesai saat approve
        if ($pinjaman->tipe === Pinjaman::TIPE_MINTA) {
            return back()->with('error', 'Permintaan barang tidak memerlukan checkout. Barang sudah diserahkan saat disetujui.');
        }

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
                'status'         => Pinjaman::STATUS_DIPINJAM,
                'checked_out_at' => now(),
                'catatan_admin'  => $validated['catatan_admin'] ?? $pinjaman->catatan_admin,
            ]);

            PinjamanLog::createLog($pinjaman->id, Auth::id(), 'checkout', 'Barang dipinjamkan ke peminjam.');

            Notification::send(
                $pinjaman->user_id,
                Notification::JENIS_PINJAMAN_STATUS,
                'Barang Sudah Diserahkan',
                "Barang untuk {$pinjaman->kode_pinjaman} sudah dapat digunakan. Harap kembalikan tepat waktu.",
                route('pinjaman.show', $pinjaman)
            );
        });

        return back()->with('success', 'Check-out berhasil. Barang sudah diserahkan ke peminjam.');
    }

    public function checkIn(Request $request, Pinjaman $pinjaman)
    {
        // Permintaan tidak memiliki tahap checkin
        if ($pinjaman->tipe === Pinjaman::TIPE_MINTA) {
            return back()->with('error', 'Permintaan barang tidak memerlukan pengembalian.');
        }

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
                'status'        => Pinjaman::STATUS_SELESAI,
                'tgl_kembali'   => now(),
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
            return back()->with('error', 'Pengajuan sudah final.');
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
                'status'        => Pinjaman::STATUS_SELESAI,
                'tgl_kembali'   => now(),
                'catatan_admin' => $validated['catatan_admin'],
            ]);

            PinjamanLog::createLog($pinjaman->id, Auth::id(), 'force_close', 'Ditutup paksa admin.');
        });

        return back()->with('success', 'Pengajuan ditutup paksa oleh admin.');
    }

    public function adjustStock(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'stok_total'    => 'required|integer|min:0',
            'stok_tersedia' => 'required|integer|min:0',
            'stok_rusak'    => 'required|integer|min:0',
        ]);

        if ($validated['stok_tersedia'] + $validated['stok_rusak'] > $validated['stok_total']) {
            return back()->with('error', 'Stok tersedia + rusak tidak boleh melebihi stok total.');
        }

        $barang->update($validated);

        return back()->with('success', 'Stok barang berhasil diperbarui.');
    }

    public function export(Request $request)
    {
        $query = Pinjaman::query()->with(['user:id,name', 'barang:id,nama,unit_sarpras,tipe_transaksi']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rows = $query->latest()->get();

        $csv = [
            ['Kode', 'Tipe', 'Peminjam', 'Barang', 'Unit Sarpras', 'Qty', 'Status', 'Pinjam', 'Jatuh Tempo', 'Kembali'],
        ];

        foreach ($rows as $row) {
            $csv[] = [
                $row->kode_pinjaman,
                $row->tipe === 'minta' ? 'Permintaan' : 'Pinjaman',
                $row->user->name ?? '-',
                $row->barang->nama ?? '-',
                $row->barang ? ($row->barang->unit_sarpras === 'bawah' ? 'Sarpras Bawah' : 'Sarpras Atas') : '-',
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
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-pinjaman-'.now()->format('Ymd-His').'.csv"',
        ]);
    }
}
