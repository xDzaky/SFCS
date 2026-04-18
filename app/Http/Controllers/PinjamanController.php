<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Notification;
use App\Models\Pinjaman;
use App\Models\PinjamanFeedback;
use App\Models\PinjamanLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PinjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pinjaman::query()
            ->with(['barang', 'feedback'])
            ->where('user_id', Auth::id())
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pinjamans = $query->paginate(10);

        return view('pinjaman.index', compact('pinjamans'));
    }

    public function create()
    {
        $barangs = Barang::query()
            ->active()
            ->where('stok_tersedia', '>', 0)
            ->orderBy('nama')
            ->get();

        return view('pinjaman.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'qty' => 'required|integer|min:1',
            'durasi_hari' => 'required|integer|min:1|max:30',
            'alasan' => 'required|string|min:10|max:1000',
        ]);

        $barang = Barang::query()->lockForUpdate()->findOrFail($validated['barang_id']);

        if (!$barang->is_active) {
            return back()->with('error', 'Barang tidak aktif untuk dipinjam.')->withInput();
        }

        if ($validated['qty'] > $barang->stok_tersedia) {
            return back()->with('error', 'Jumlah pinjam melebihi stok tersedia.')->withInput();
        }

        DB::transaction(function () use ($validated): void {
            $now = now();
            $pinjaman = Pinjaman::create([
                'user_id' => Auth::id(),
                'barang_id' => $validated['barang_id'],
                'qty' => $validated['qty'],
                'tgl_pinjam' => $now,
                'tgl_jatuh_tempo' => Carbon::parse($now)->addDays((int) $validated['durasi_hari']),
                'status' => Pinjaman::STATUS_PENDING,
                'alasan' => $validated['alasan'],
            ]);

            PinjamanLog::createLog(
                $pinjaman->id,
                Auth::id(),
                'create',
                "Pengajuan pinjaman {$pinjaman->kode_pinjaman} dibuat"
            );

            $admins = User::query()->whereIn('role', ['admin', 'superadmin'])->pluck('id');
            foreach ($admins as $adminId) {
                Notification::send(
                    $adminId,
                    Notification::JENIS_PINJAMAN_CREATED,
                    'Pengajuan Pinjaman Baru',
                    "Pengajuan {$pinjaman->kode_pinjaman} menunggu persetujuan admin.",
                    route('admin.pinjaman.show', $pinjaman)
                );
            }
        });

        return redirect()->route('pinjaman.index')->with('success', 'Pengajuan pinjaman berhasil dikirim.');
    }

    public function show(Pinjaman $pinjaman)
    {
        if ((int) $pinjaman->user_id !== (int) Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $pinjaman->load(['barang', 'logs.user', 'feedback']);

        return view('pinjaman.show', compact('pinjaman'));
    }

    public function cancel(Pinjaman $pinjaman)
    {
        if ((int) $pinjaman->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($pinjaman->status !== Pinjaman::STATUS_PENDING) {
            return back()->with('error', 'Hanya pinjaman pending yang bisa dibatalkan.');
        }

        $pinjaman->update(['status' => Pinjaman::STATUS_DITOLAK, 'catatan_admin' => 'Dibatalkan peminjam.']);
        PinjamanLog::createLog($pinjaman->id, Auth::id(), 'cancel', 'Pengajuan dibatalkan peminjam.');

        return back()->with('success', 'Pengajuan pinjaman berhasil dibatalkan.');
    }

    public function submitFeedback(Request $request, Pinjaman $pinjaman)
    {
        if ((int) $pinjaman->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($pinjaman->status !== Pinjaman::STATUS_SELESAI) {
            return back()->with('error', 'Feedback hanya bisa diberikan setelah pinjaman selesai.');
        }

        if ($pinjaman->feedback) {
            return back()->with('error', 'Feedback untuk pinjaman ini sudah diberikan.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ]);

        PinjamanFeedback::create([
            'pinjaman_id' => $pinjaman->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'komentar' => $validated['komentar'] ?? null,
        ]);

        PinjamanLog::createLog($pinjaman->id, Auth::id(), 'feedback', 'Feedback peminjaman diberikan.');

        return back()->with('success', 'Terima kasih, feedback Anda sudah tersimpan.');
    }
}
