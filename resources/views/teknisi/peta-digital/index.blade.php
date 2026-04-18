@extends('layouts.sfcs')

@section('title', 'Peta Digital')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Peta Digital</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Peta Digital Sekolah</h1>
    <p class="page-subtitle">Gunakan denah aktif untuk melihat area ruang dan tiket aktif yang sudah dipetakan.</p>
</div>

@include('partials.school-map-viewer', ['viewerId' => 'teknisi-map-viewer'])
@endsection
