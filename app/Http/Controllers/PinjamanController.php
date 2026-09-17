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

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $pinjamans = $query->paginate(10);

        return view('pinjaman.index', compact('pinjamans'));
    }

    public function create()
    {
        $barangs = Barang::query()
            ->active()
            ->where('stok_tersedia', '>', 0)
            ->orderBy('unit_sarpras')
            ->orderBy('nama')
            ->get()
            ->groupBy('unit_sarpras');

        return view('pinjaman.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $barang = Barang::query()->findOrFail($request->input('barang_id'));
        $isPermintaan = $barang->tipe_transaksi === 'minta';

        $rules = [
            'barang_id' => 'required|exists:barangs,id',
            'qty'       => 'required|integer|min:1',
            'alasan'    => 'required|string|min:10|max:1000',
        ];

        // Durasi hanya diperlukan untuk pinjaman (bukan permintaan)
        if (!$isPermintaan) {
            $rules['durasi_hari'] = 'required|integer|min:1|max:30';
        }

        $validated = $request->validate($rules);

        $barang = Barang::query()->lockForUpdate()->findOrFail($validated['barang_id']);

        if (!$barang->is_active) {
            return back()->with('error', 'Barang tidak aktif.')->withInput();
        }

        if ($validated['qty'] > $barang->stok_tersedia) {
            return back()->with('error', 'Jumlah melebihi stok tersedia.')->withInput();
        }

        DB::transaction(function () use ($validated, $barang, $isPermintaan): void {
            $now = now();

            $pinjaman = Pinjaman::create([
                'user_id'        => Auth::id(),
                'barang_id'      => $barang->id,
                'qty'            => $validated['qty'],
                'tipe'           => $barang->tipe_transaksi,
                'tgl_pinjam'     => $now,
                'tgl_jatuh_tempo' => $isPermintaan
                    ? $now // permintaan: jatuh tempo = hari ini (tidak relevan)
                    : Carbon::parse($now)->addDays((int) $validated['durasi_hari']),
                'status'         => Pinjaman::STATUS_PENDING,
                'alasan'         => $validated['alasan'],
            ]);

            PinjamanLog::createLog(
                $pinjaman->id,
                Auth::id(),
                'create',
                ($isPermintaan ? "Pengajuan permintaan" : "Pengajuan pinjaman") . " {$pinjaman->kode_pinjaman} dibuat"
            );

            $admins = User::query()->whereIn('role', ['admin', 'superadmin'])->pluck('id');
            foreach ($admins as $adminId) {
                Notification::send(
                    $adminId,
                    Notification::JENIS_PINJAMAN_CREATED,
                    $isPermintaan ? 'Permintaan Barang Baru' : 'Pengajuan Pinjaman Baru',
                    "Pengajuan {$pinjaman->kode_pinjaman} menunggu persetujuan.",
                    route('admin.pinjaman.show', $pinjaman)
                );
            }
        });

        $msg = $isPermintaan
            ? 'Permintaan barang berhasil dikirim. Tunggu persetujuan admin.'
            : 'Pengajuan pinjaman berhasil dikirim.';

        return redirect()->route('pinjaman.index')->with('success', $msg);
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
            return back()->with('error', 'Hanya pengajuan pending yang bisa dibatalkan.');
        }

        $pinjaman->update(['status' => Pinjaman::STATUS_DITOLAK, 'catatan_admin' => 'Dibatalkan peminjam.']);
        PinjamanLog::createLog($pinjaman->id, Auth::id(), 'cancel', 'Pengajuan dibatalkan peminjam.');

        return back()->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    public function submitFeedback(Request $request, Pinjaman $pinjaman)
    {
        if ((int) $pinjaman->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($pinjaman->status !== Pinjaman::STATUS_SELESAI) {
            return back()->with('error', 'Feedback hanya bisa diberikan setelah transaksi selesai.');
        }

        if ($pinjaman->feedback) {
            return back()->with('error', 'Feedback sudah diberikan.');
        }

        $validated = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ]);

        PinjamanFeedback::create([
            'pinjaman_id' => $pinjaman->id,
            'user_id'     => Auth::id(),
            'rating'      => $validated['rating'],
            'komentar'    => $validated['komentar'] ?? null,
        ]);

        PinjamanLog::createLog($pinjaman->id, Auth::id(), 'feedback', 'Feedback diberikan.');

        return back()->with('success', 'Terima kasih, feedback Anda sudah tersimpan.');
    }
}
