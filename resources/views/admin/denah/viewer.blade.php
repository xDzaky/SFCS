@extends('layouts.sfcs')

@section('title', 'Peta Digital')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Peta Digital</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Peta Digital Sekolah</h1>
        <p class="page-subtitle">Pantau area ruang yang sudah dipetakan dan lihat tiket aktif yang sudah punya lokasi denah.</p>
    </div>
    <a href="{{ route('admin.denah.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-draw-polygon me-2"></i>Kelola Denah
    </a>
</div>

@include('partials.school-map-viewer', ['viewerId' => 'admin-map-viewer'])
@endsection
