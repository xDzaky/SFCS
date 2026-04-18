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
        $statsQuery = Barang::query();
        $barangsQuery = Barang::query()->withCount('pinjamans')->latest();

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

        $stats = [
            'total_jenis' => (int) $statsQuery->count(),
            'aktif' => (int) Barang::query()->where('is_active', true)->count(),
            'stok_total' => (int) Barang::query()->sum('stok_total'),
            'stok_tersedia' => (int) Barang::query()->sum('stok_tersedia'),
            'stok_rusak' => (int) Barang::query()->sum('stok_rusak'),
        ];

        return view('admin.barangs.index', compact('barangs', 'categories', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBarang($request);

        Barang::create([
            'kode_barang' => strtoupper((string) $validated['kode_barang']),
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'] ?? null,
            'lokasi' => $validated['lokasi'] ?? null,
            'stok_total' => $validated['stok_total'],
            'stok_tersedia' => $validated['stok_tersedia'],
            'stok_rusak' => $validated['stok_rusak'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.barangs.index')
            ->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function update(Request $request, Barang $barang): RedirectResponse
    {
        $validated = $this->validateBarang($request, $barang);

        $barang->update([
            'kode_barang' => strtoupper((string) $validated['kode_barang']),
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'] ?? null,
            'lokasi' => $validated['lokasi'] ?? null,
            'stok_total' => $validated['stok_total'],
            'stok_tersedia' => $validated['stok_tersedia'],
            'stok_rusak' => $validated['stok_rusak'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.barangs.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        if ($barang->pinjamans()->exists()) {
            return back()->with('error', 'Barang sudah pernah dipakai pada transaksi pinjaman. Nonaktifkan saja jika tidak ingin dipakai lagi.');
        }

        $barang->delete();

        return redirect()->route('admin.barangs.index')
            ->with('success', 'Barang berhasil dihapus.');
    }

    public function toggleStatus(Barang $barang): RedirectResponse
    {
        $barang->update([
            'is_active' => !$barang->is_active,
        ]);

        $statusLabel = $barang->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Barang berhasil {$statusLabel}.");
    }

    private function validateBarang(Request $request, ?Barang $barang = null): array
    {
        $validated = $request->validate([
            'kode_barang' => [
                'required',
                'string',
                'max:50',
                Rule::unique('barangs', 'kode_barang')->ignore($barang?->id),
            ],
            'nama' => 'required|string|max:150',
            'kategori' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:150',
            'stok_total' => 'required|integer|min:0',
            'stok_tersedia' => 'required|integer|min:0',
            'stok_rusak' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ((int) $validated['stok_tersedia'] + (int) $validated['stok_rusak'] > (int) $validated['stok_total']) {
            throw ValidationException::withMessages([
                'stok_tersedia' => 'Stok tersedia + stok rusak tidak boleh melebihi stok total.',
            ]);
        }

        return $validated;
    }
}
