@extends('layouts.sfcs')

@section('title', 'Ajukan Pinjaman / Permintaan Barang')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pinjaman.index') }}">Pinjam & Minta Barang</a></li>
    <li class="breadcrumb-item active">Ajukan</li>
</ol>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        {{-- Info panel --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0d6efd!important">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10" style="width:44px;height:44px">
                                <i class="fas fa-projector text-primary fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Sarpras Atas</div>
                                <span class="badge bg-primary">Peminjaman</span>
                            </div>
                        </div>
                        <p class="small text-muted mb-0">Proyektor, Kabel HDMI, Mic, Speaker, Laptop, dll.<br>
                        <strong>Barang dikembalikan</strong> setelah selesai digunakan.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #198754!important">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:44px;height:44px">
                                <i class="fas fa-box-open text-success fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Sarpras Bawah</div>
                                <span class="badge bg-success">Permintaan</span>
                            </div>
                        </div>
                        <p class="small text-muted mb-0">Kertas HVS, Spidol, ATK, bahan pembelajaran, dll.<br>
                        <strong>Barang tidak dikembalikan</strong> — untuk kegiatan belajar.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header fw-semibold">
                <i class="fas fa-file-alt me-2"></i>Form Pengajuan
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('pinjaman.store') }}" method="POST" id="formPinjaman">
                    @csrf

                    {{-- Pilih Barang --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pilih Barang</label>
                        <select name="barang_id" class="form-select" id="barangSelect" required onchange="onBarangChange(this)">
                            <option value="">-- Pilih barang --</option>
                            @foreach(['atas' => 'Sarpras Atas (Peminjaman)', 'bawah' => 'Sarpras Bawah (Permintaan)'] as $unit => $label)
                                @if(isset($barangs[$unit]) && $barangs[$unit]->count())
                                    <optgroup label="{{ $label }}">
                                        @foreach($barangs[$unit] as $barang)
                                            <option value="{{ $barang->id }}"
                                                data-tipe="{{ $barang->tipe_transaksi }}"
                                                data-unit="{{ $barang->unit_sarpras }}"
                                                data-stok="{{ $barang->stok_tersedia }}"
                                                data-label-unit="{{ $barang->label_unit_sarpras }}"
                                                {{ old('barang_id') == $barang->id ? 'selected' : '' }}>
                                                {{ $barang->nama }} — Stok: {{ $barang->stok_tersedia }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Info barang yang dipilih --}}
                    <div id="barangInfo" class="alert alert-light border d-none mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <span id="barangInfoBadge" class="badge"></span>
                            <span id="barangInfoText" class="small"></span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <input type="number" min="1" name="qty" id="qtyInput" class="form-control"
                                value="{{ old('qty', 1) }}" required>
                        </div>

                        {{-- Durasi hanya untuk pinjaman, bukan permintaan --}}
                        <div class="col-md-8" id="durasiWrap">
                            <label class="form-label fw-semibold">Durasi Pinjam (Hari)</label>
                            <input type="number" min="1" max="30" name="durasi_hari" id="durasiInput"
                                class="form-control" value="{{ old('durasi_hari', 1) }}">
                            <div class="form-text">Maksimal 30 hari.</div>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold">Alasan / Keperluan</label>
                        <textarea name="alasan" rows="4" class="form-control" minlength="10"
                            placeholder="Jelaskan keperluan Anda, misal: untuk presentasi di kelas XII RPL 1..." required>{{ old('alasan') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane me-1"></i>Kirim Pengajuan
                        </button>
                        <a href="{{ route('pinjaman.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function onBarangChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const tipe = opt.dataset.tipe;
    const unit = opt.dataset.unit;
    const stok = opt.dataset.stok;
    const labelUnit = opt.dataset.labelUnit;

    const durasiWrap = document.getElementById('durasiWrap');
    const durasiInput = document.getElementById('durasiInput');
    const info = document.getElementById('barangInfo');
    const badge = document.getElementById('barangInfoBadge');
    const text = document.getElementById('barangInfoText');

    if (!opt.value) {
        info.classList.add('d-none');
        durasiWrap.style.display = '';
        durasiInput.required = true;
        return;
    }

    info.classList.remove('d-none');

    if (tipe === 'minta') {
        badge.textContent = 'Permintaan · ' + labelUnit;
        badge.className = 'badge bg-success';
        text.textContent = 'Barang ini merupakan bahan habis pakai. Tidak perlu dikembalikan. Stok tersedia: ' + stok;
        durasiWrap.style.display = 'none';
        durasiInput.required = false;
        durasiInput.value = '';
    } else {
        badge.textContent = 'Peminjaman · ' + labelUnit;
        badge.className = 'badge bg-primary';
        text.textContent = 'Barang harus dikembalikan sesuai durasi yang dipilih. Stok tersedia: ' + stok;
        durasiWrap.style.display = '';
        durasiInput.required = true;
    }
}

// Trigger on page load if old() value set
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('barangSelect');
    if (sel.value) onBarangChange(sel);
});
</script>
@endpush
