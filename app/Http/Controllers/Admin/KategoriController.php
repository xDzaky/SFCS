<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\SubKategori;
use App\Models\Log;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Display a listing of kategoris
     */
    public function index()
    {
        $kategoris = Kategori::withCount('pengaduans')
            ->latest()
            ->paginate(15);

        return view('admin.kategoris.index', compact('kategoris'));
    }

    /**
     * Show the form for creating a new kategori
     */
    public function create()
    {
        return redirect()->route('admin.kategoris.index');
    }

    /**
     * Store a newly created kategori
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategoris',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $kategori = Kategori::create([
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'icon' => $validated['icon'] ?? 'fa-folder',
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return redirect()->route('admin.kategoris.index')
            ->with('success', 'Kategori berhasil dibuat');
    }

    /**
     * Show the form for editing the specified kategori
     */
    public function edit(Kategori $kategori)
    {
        return view('admin.kategoris.edit', compact('kategori'));
    }

    /**
     * Update the specified kategori
     */
    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategoris,nama,' . $kategori->id,
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $kategori->update([
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'icon' => $validated['icon'] ?? 'fa-folder',
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return redirect()->route('admin.kategoris.index')
            ->with('success', 'Kategori berhasil diperbarui');
    }

    /**
     * Remove the specified kategori
     */
    public function destroy(Kategori $kategori)
    {
        if ($kategori->pengaduans()->exists()) {
            return back()->with('error', 'Kategori memiliki pengaduan dan tidak dapat dihapus');
        }


        $kategori->delete();

        return redirect()->route('admin.kategoris.index')
            ->with('success', 'Kategori berhasil dihapus');
    }

    /**
     * Show sub kategoris of a kategori
     */
    public function subKategoris(Kategori $kategori)
    {
        $subKategoris = $kategori->subKategoris()->paginate(15);

        return view('admin.kategoris.sub-kategoris', compact('kategori', 'subKategoris'));
    }

    /**
     * Store a new sub kategori
     */
    public function storeSubKategori(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subKategori = $kategori->subKategoris()->create([
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return back()->with('success', 'Sub Kategori berhasil dibuat');
    }

    /**
     * Update a sub kategori
     */
    public function updateSubKategori(Request $request, SubKategori $subKategori)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subKategori->update([
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return back()->with('success', 'Sub Kategori berhasil diperbarui');
    }

    /**
     * Delete a sub kategori
     */
    public function destroySubKategori(SubKategori $subKategori)
    {
        if ($subKategori->pengaduans()->exists()) {
            return back()->with('error', 'Sub Kategori memiliki pengaduan dan tidak dapat dihapus');
        }


        $subKategori->delete();

        return back()->with('success', 'Sub Kategori berhasil dihapus');
    }
}
