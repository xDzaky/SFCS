<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use App\Models\Ruangan;
use App\Models\Log;
use Illuminate\Http\Request;

class GedungController extends Controller
{
    /**
     * Display a listing of gedungs
     */
    public function index()
    {
        $gedungs = Gedung::withCount('pengaduans')->latest()->paginate(15);

        return view('admin.gedungs.index', compact('gedungs'));
    }

    /**
     * Show the form for creating a new gedung
     */
    public function create()
    {
        return redirect()->route('admin.gedungs.index');
    }

    /**
     * Store a newly created gedung
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:gedungs',
            'kode' => 'required|string|max:10|unique:gedungs',
            'deskripsi' => 'nullable|string',
            'jumlah_lantai' => 'required|integer|min:1|max:20',
            'is_active' => 'boolean',
        ]);

        $gedung = Gedung::create([
            'nama' => $validated['nama'],
            'kode' => strtoupper($validated['kode']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'jumlah_lantai' => $validated['jumlah_lantai'],
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return redirect()->route('admin.gedungs.index')
            ->with('success', 'Gedung berhasil dibuat');
    }

    /**
     * Show the form for editing the specified gedung
     */
    public function edit(Gedung $gedung)
    {
        return view('admin.gedungs.edit', compact('gedung'));
    }

    /**
     * Update the specified gedung
     */
    public function update(Request $request, Gedung $gedung)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:gedungs,nama,' . $gedung->id,
            'kode' => 'required|string|max:10|unique:gedungs,kode,' . $gedung->id,
            'deskripsi' => 'nullable|string',
            'jumlah_lantai' => 'required|integer|min:1|max:20',
            'is_active' => 'boolean',
        ]);

        $gedung->update([
            'nama' => $validated['nama'],
            'kode' => strtoupper($validated['kode']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'jumlah_lantai' => $validated['jumlah_lantai'],
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return redirect()->route('admin.gedungs.index')
            ->with('success', 'Gedung berhasil diperbarui');
    }

    /**
     * Remove the specified gedung
     */
    public function destroy(Gedung $gedung)
    {
        // Check if gedung has ruangans with pengaduans
        $hasPengaduan = Ruangan::where('gedung_id', $gedung->id)
            ->whereHas('pengaduans')
            ->exists();

        if ($hasPengaduan) {
            return back()->with('error', 'Gedung memiliki ruangan dengan pengaduan dan tidak dapat dihapus');
        }


        // Delete ruangans first
        $gedung->ruangans()->delete();
        $gedung->delete();

        return redirect()->route('admin.gedungs.index')
            ->with('success', 'Gedung berhasil dihapus');
    }

    /**
     * Show ruangans of a gedung
     */
    public function ruangans(Gedung $gedung)
    {
        $ruangans = $gedung->ruangans()
            ->withCount('pengaduans')
            ->paginate(15);

        return view('admin.gedungs.ruangans', compact('gedung', 'ruangans'));
    }

    /**
     * Store a new ruangan
     */
    public function storeRuangan(Request $request, Gedung $gedung)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20',
            'lantai' => 'required|integer|min:1|max:' . $gedung->jumlah_lantai,
            'kapasitas' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check unique kode within gedung
        $exists = Ruangan::where('gedung_id', $gedung->id)
            ->where('kode', $validated['kode'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kode ruangan sudah digunakan di gedung ini');
        }

        $ruangan = $gedung->ruangans()->create([
            'nama' => $validated['nama'],
            'kode' => strtoupper($validated['kode']),
            'lantai' => $validated['lantai'],
            'kapasitas' => $validated['kapasitas'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return back()->with('success', 'Ruangan berhasil dibuat');
    }

    /**
     * Update a ruangan
     */
    public function updateRuangan(Request $request, Ruangan $ruangan)
    {
        $gedung = $ruangan->gedung;

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20',
            'lantai' => 'required|integer|min:1|max:' . $gedung->jumlah_lantai,
            'kapasitas' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check unique kode within gedung (excluding self)
        $exists = Ruangan::where('gedung_id', $gedung->id)
            ->where('kode', $validated['kode'])
            ->where('id', '!=', $ruangan->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kode ruangan sudah digunakan di gedung ini');
        }

        $ruangan->update([
            'nama' => $validated['nama'],
            'kode' => strtoupper($validated['kode']),
            'lantai' => $validated['lantai'],
            'kapasitas' => $validated['kapasitas'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);


        return back()->with('success', 'Ruangan berhasil diperbarui');
    }

    /**
     * Delete a ruangan
     */
    public function destroyRuangan(Ruangan $ruangan)
    {
        if ($ruangan->pengaduans()->exists()) {
            return back()->with('error', 'Ruangan memiliki pengaduan dan tidak dapat dihapus');
        }


        $ruangan->delete();

        return back()->with('success', 'Ruangan berhasil dihapus');
    }
}
