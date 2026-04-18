@extends('layouts.sfcs')

@section('title', 'Ajukan Pinjaman')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pinjaman.index') }}">Pinjam Barang</a></li>
    <li class="breadcrumb-item active">Ajukan</li>
</ol>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Form Pengajuan Pinjaman</div>
            <div class="card-body">
                <form action="{{ route('pinjaman.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select name="barang_id" class="form-select" required>
                            <option value="">Pilih barang</option>
                            @foreach($barangs as $barang)
                                <option value="{{ $barang->id }}" {{ old('barang_id') == $barang->id ? 'selected' : '' }}>
                                    {{ $barang->nama }} (Stok: {{ $barang->stok_tersedia }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Qty</label>
                            <input type="number" min="1" name="qty" class="form-control" value="{{ old('qty', 1) }}" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Durasi (Hari)</label>
                            <input type="number" min="1" max="30" name="durasi_hari" class="form-control" value="{{ old('durasi_hari', 1) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alasan Pinjam</label>
                        <textarea name="alasan" rows="4" class="form-control" minlength="10" required>{{ old('alasan') }}</textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">Kirim Pengajuan</button>
                    <a href="{{ route('pinjaman.index') }}" class="btn btn-outline-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
