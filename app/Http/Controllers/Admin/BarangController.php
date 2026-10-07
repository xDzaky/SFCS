<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BarangController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $barangsQuery = Barang::query()->withCount('pinjamans')->latest();

        // Auto-filter berdasarkan role:
        // sarpras_atas → hanya lihat barang unit 'atas'
        // sarpras_bawah → hanya lihat barang unit 'bawah'
        // admin/superadmin → bisa lihat semua
        if ($user->isSarprasAtas() && !$user->isAdmin()) {
            $barangsQuery->where('unit_sarpras', 'atas');
        } elseif ($user->isSarprasBawah()) {
            $barangsQuery->where('unit_sarpras', 'bawah');
        } elseif ($request->filled('unit_sarpras')) {
            // Admin: filter manual via request param
            $barangsQuery->where('unit_sarpras', $request->input('unit_sarpras'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $barangsQuery->where(function ($query) use ($search): void {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $barangsQuery->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('kategori')) {
            $barangsQuery->where('kategori', 'like', '%'.$request->input('kategori').'%');
        }

        $barangs = $barangsQuery->paginate(12)->withQueryString();

        $categories = Barang::query()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->orderBy('kategori')
            ->distinct()
            ->pluck('kategori');

        // Build stats scoped to user's unit
        $statsQuery = Barang::query();
        if ($user->isSarprasAtas() && !$user->isAdmin()) {
            $statsQuery->where('unit_sarpras', 'atas');
        } elseif ($user->isSarprasBawah()) {
            $statsQuery->where('unit_sarpras', 'bawah');
        }

        $stats = [
            'total_jenis'   => (int) (clone $statsQuery)->count(),
            'aktif'         => (int) (clone $statsQuery)->where('is_active', true)->count(),
            'stok_total'    => (int) (clone $statsQuery)->sum('stok_total'),
            'stok_tersedia' => (int) (clone $statsQuery)->sum('stok_tersedia'),
            'stok_rusak'    => (int) (clone $statsQuery)->sum('stok_rusak'),
            'jml_atas'      => (int) Barang::query()->where('unit_sarpras', 'atas')->count(),
            'jml_bawah'     => (int) Barang::query()->where('unit_sarpras', 'bawah')->count(),
        ];

        return view('admin.barangs.index', compact('barangs', 'categories', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $this->validateBarang($request);

        $unitSarpras = $validated['unit_sarpras'] ?? 'atas';
        $tipeTransaksi = $validated['tipe_transaksi'] ?? 'pinjam';

        // Override berdasarkan role agar aman dan otomatis
        if ($user->isSarprasBawah()) {
            $unitSarpras = 'bawah';
            $tipeTransaksi = 'minta';
        } elseif ($user->isSarprasAtas() && !$user->isAdmin()) {
            $unitSarpras = 'atas';
        }

        Barang::create([
            'kode_barang'    => strtoupper((string) $validated['kode_barang']),
            'nama'           => $validated['nama'],
            'kategori'       => $validated['kategori'] ?? null,
            'lokasi'         => $validated['lokasi'] ?? null,
            'unit_sarpras'   => $unitSarpras,
            'tipe_transaksi' => $tipeTransaksi,
            'stok_total'     => $validated['stok_total'],
            'stok_tersedia'  => $validated['stok_tersedia'],
            'stok_rusak'     => $validated['stok_rusak'],
            'is_active'      => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.barangs.index')
            ->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function update(Request $request, Barang $barang): RedirectResponse
    {
        $user = auth()->user();

        // Cek otorisasi edit: sarpras_bawah hanya bisa edit barang bawah, sarpras_atas hanya barang atas
        if ($user->isSarprasBawah() && $barang->unit_sarpras !== 'bawah') {
            abort(403, 'Anda hanya dapat mengelola barang Sarpras Bawah.');
        }
        if ($user->isSarprasAtas() && !$user->isAdmin() && $barang->unit_sarpras !== 'atas') {
            abort(403, 'Anda hanya dapat mengelola barang Sarpras Atas.');
        }

        $validated = $this->validateBarang($request, $barang);

        $unitSarpras = $validated['unit_sarpras'] ?? $barang->unit_sarpras;
        $tipeTransaksi = $validated['tipe_transaksi'] ?? $barang->tipe_transaksi;

        if ($user->isSarprasBawah()) {
            $unitSarpras = 'bawah';
            $tipeTransaksi = 'minta';
        } elseif ($user->isSarprasAtas() && !$user->isAdmin()) {
            $unitSarpras = 'atas';
        }

        $barang->update([
            'kode_barang'    => strtoupper((string) $validated['kode_barang']),
            'nama'           => $validated['nama'],
            'kategori'       => $validated['kategori'] ?? null,
            'lokasi'         => $validated['lokasi'] ?? null,
            'unit_sarpras'   => $unitSarpras,
            'tipe_transaksi' => $tipeTransaksi,
            'stok_total'     => $validated['stok_total'],
            'stok_tersedia'  => $validated['stok_tersedia'],
            'stok_rusak'     => $validated['stok_rusak'],
            'is_active'      => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.barangs.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isSarprasBawah() && $barang->unit_sarpras !== 'bawah') {
            abort(403, 'Anda hanya dapat mengelola barang Sarpras Bawah.');
        }
        if ($user->isSarprasAtas() && !$user->isAdmin() && $barang->unit_sarpras !== 'atas') {
            abort(403, 'Anda hanya dapat mengelola barang Sarpras Atas.');
        }

        if ($barang->pinjamans()->exists()) {
            return back()->with('error', 'Barang sudah pernah dipakai pada transaksi. Nonaktifkan saja jika tidak ingin dipakai lagi.');
        }

        $barang->delete();

        return redirect()->route('admin.barangs.index')
            ->with('success', 'Barang berhasil dihapus.');
    }

    public function toggleStatus(Barang $barang): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isSarprasBawah() && $barang->unit_sarpras !== 'bawah') {
            abort(403, 'Anda hanya dapat mengelola barang Sarpras Bawah.');
        }
        if ($user->isSarprasAtas() && !$user->isAdmin() && $barang->unit_sarpras !== 'atas') {
            abort(403, 'Anda hanya dapat mengelola barang Sarpras Atas.');
        }

        $barang->update(['is_active' => !$barang->is_active]);
        $statusLabel = $barang->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Barang berhasil {$statusLabel}.");
    }

    private function validateBarang(Request $request, ?Barang $barang = null): array
    {
        $user = auth()->user();

        $rules = [
            'kode_barang' => [
                'required', 'string', 'max:50',
                Rule::unique('barangs', 'kode_barang')->ignore($barang?->id),
            ],
            'nama'           => 'required|string|max:150',
            'kategori'       => 'nullable|string|max:100',
            'lokasi'         => 'nullable|string|max:150',
            'stok_total'     => 'required|integer|min:0',
            'stok_tersedia'  => 'required|integer|min:0',
            'stok_rusak'     => 'required|integer|min:0',
            'is_active'      => 'nullable|boolean',
        ];

        // Hanya wajibkan input unit_sarpras dan tipe_transaksi jika admin
        if ($user && $user->isAdmin()) {
            $rules['unit_sarpras'] = 'required|in:atas,bawah';
            $rules['tipe_transaksi'] = 'required|in:pinjam,minta';
        } else {
            $rules['unit_sarpras'] = 'nullable|in:atas,bawah';
            $rules['tipe_transaksi'] = 'nullable|in:pinjam,minta';
        }

        $validated = $request->validate($rules);

        if ((int) $validated['stok_tersedia'] + (int) $validated['stok_rusak'] > (int) $validated['stok_total']) {
            throw ValidationException::withMessages([
                'stok_tersedia' => 'Stok tersedia + stok rusak tidak boleh melebihi stok total.',
            ]);
        }

        return $validated;
    }
}
