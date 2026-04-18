@php
    $barang = $barang ?? null;
    $prefix = $prefix ?? 'barang';
    $defaults = [
        'kode_barang' => old('kode_barang', $barang->kode_barang ?? ''),
        'nama' => old('nama', $barang->nama ?? ''),
        'kategori' => old('kategori', $barang->kategori ?? ''),
        'lokasi' => old('lokasi', $barang->lokasi ?? ''),
        'stok_total' => old('stok_total', $barang->stok_total ?? 0),
        'stok_tersedia' => old('stok_tersedia', $barang->stok_tersedia ?? 0),
        'stok_rusak' => old('stok_rusak', $barang->stok_rusak ?? 0),
        'is_active' => (int) old('is_active', $barang->is_active ?? true),
    ];
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label for="{{ $prefix }}_kode_barang" class="form-label">Kode Barang</label>
        <input type="text" id="{{ $prefix }}_kode_barang" name="kode_barang" class="form-control" value="{{ $defaults['kode_barang'] }}" required>
    </div>
    <div class="col-md-8">
        <label for="{{ $prefix }}_nama" class="form-label">Nama Barang</label>
        <input type="text" id="{{ $prefix }}_nama" name="nama" class="form-control" value="{{ $defaults['nama'] }}" required>
    </div>
    <div class="col-md-6">
        <label for="{{ $prefix }}_kategori" class="form-label">Kategori</label>
        <input type="text" id="{{ $prefix }}_kategori" name="kategori" class="form-control" value="{{ $defaults['kategori'] }}" placeholder="Contoh: ATK">
    </div>
    <div class="col-md-6">
        <label for="{{ $prefix }}_lokasi" class="form-label">Lokasi Penyimpanan</label>
        <input type="text" id="{{ $prefix }}_lokasi" name="lokasi" class="form-control" value="{{ $defaults['lokasi'] }}" placeholder="Contoh: Gudang ATK">
    </div>
    <div class="col-md-4">
        <label for="{{ $prefix }}_stok_total" class="form-label">Stok Total</label>
        <input type="number" id="{{ $prefix }}_stok_total" name="stok_total" class="form-control" min="0" value="{{ $defaults['stok_total'] }}" required>
    </div>
    <div class="col-md-4">
        <label for="{{ $prefix }}_stok_tersedia" class="form-label">Stok Tersedia</label>
        <input type="number" id="{{ $prefix }}_stok_tersedia" name="stok_tersedia" class="form-control" min="0" value="{{ $defaults['stok_tersedia'] }}" required>
    </div>
    <div class="col-md-4">
        <label for="{{ $prefix }}_stok_rusak" class="form-label">Stok Rusak</label>
        <input type="number" id="{{ $prefix }}_stok_rusak" name="stok_rusak" class="form-control" min="0" value="{{ $defaults['stok_rusak'] }}" required>
    </div>
    <div class="col-12">
        <label for="{{ $prefix }}_is_active" class="form-label">Status</label>
        <select id="{{ $prefix }}_is_active" name="is_active" class="form-select">
            <option value="1" {{ $defaults['is_active'] === 1 ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ $defaults['is_active'] === 0 ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </div>
</div>
