@extends('layouts.app')

@section('title', 'Daftar Aksesi - Bank Benih Pusaka')

@section('content')
<div class="page-head">
    <div>
        <h1 class="mb-0">Daftar aksesi benih</h1>
        <p>Kelola koleksi varietas lokal beserta asal pengumpulan dan perlakuan penyimpanannya.</p>
    </div>
    <a href="{{ route('accessions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah aksesi</a>
</div>

{{-- Ringkasan koleksi --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="panel stat"><div class="num" data-count="{{ $stats['total'] }}">0</div><div class="lbl">aksesi<br>tersimpan</div></div></div>
    <div class="col-6 col-lg-3"><div class="panel stat"><div class="num" data-count="{{ $stats['groups'] }}">0</div><div class="lbl">kelompok<br>tanaman</div></div></div>
    <div class="col-6 col-lg-3"><div class="panel stat"><div class="num" data-count="{{ $stats['provinces'] }}">0</div><div class="lbl">provinsi<br>asal</div></div></div>
    <div class="col-6 col-lg-3"><div class="panel stat"><div class="num" data-count="{{ $stats['treatments'] }}">0</div><div class="lbl">jenis perlakuan<br>terpakai</div></div></div>
</div>

{{-- Pil kelompok: klik untuk menyaring cepat --}}
<div class="pills mb-3" id="group-pills" role="group" aria-label="Saring menurut kelompok tanaman">
    <button type="button" class="pill active" data-group="">Semua <span class="count">{{ $stats['total'] }}</span></button>
    @foreach ($plantGroups as $g)
        <button type="button" class="pill" data-group="{{ $g->id }}">
            <span class="dot g-{{ $g->id % 6 }}"></span>{{ $g->name }} <span class="count">{{ $g->accessions_count }}</span>
        </button>
    @endforeach
</div>

{{-- Pencarian & filter: hasil diperbarui langsung tanpa memuat ulang halaman --}}
<form id="filter-form" method="GET" action="{{ route('accessions.index') }}" class="panel p-3 mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-lg-4">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control"
                       placeholder="Cari kode, nama, atau lokasi asal" autocomplete="off" aria-label="Cari aksesi">
                <kbd>/</kbd>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <select name="plant_group_id" class="form-select" aria-label="Kelompok tanaman">
                <option value="">Semua kelompok</option>
                @foreach ($plantGroups as $g)
                    <option value="{{ $g->id }}" @selected(($filters['plant_group_id'] ?? '') == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <select name="treatment_id" class="form-select" aria-label="Perlakuan penyimpanan">
                <option value="">Semua perlakuan</option>
                @foreach ($treatments as $t)
                    <option value="{{ $t->id }}" @selected(($filters['treatment_id'] ?? '') == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <select name="province" class="form-select" aria-label="Provinsi asal">
                <option value="">Semua provinsi</option>
                @foreach ($provinces as $p)
                    <option value="{{ $p }}" @selected(($filters['province'] ?? '') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 d-flex justify-content-lg-end gap-2">
            <button type="button" class="btn btn-outline-secondary" data-reset title="Hapus semua filter"><i class="bi bi-arrow-counterclockwise"></i></button>
            <div class="btn-group view-toggle" role="group" aria-label="Tampilan">
                <button type="button" class="btn btn-outline-secondary active" data-view="table" aria-pressed="true" title="Tampilan tabel"><i class="bi bi-list-ul"></i></button>
                <button type="button" class="btn btn-outline-secondary" data-view="grid" aria-pressed="false" title="Tampilan kartu"><i class="bi bi-grid-3x2-gap"></i></button>
            </div>
        </div>
    </div>
</form>

<div id="list">
    @include('accessions._list')
</div>
@endsection
