@extends('layouts.app')

@section('title', $accession->name . ' - Bank Benih Pusaka')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('accessions.index') }}">Daftar aksesi</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $accession->code }}</li>
    </ol>
</nav>

<div class="page-head">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="spec-tag lg">{{ $accession->code }}</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-copy="{{ $accession->code }}" title="Salin kode"><i class="bi bi-clipboard"></i></button>
        </div>
        <h1 class="mb-1">{{ $accession->name }}</h1>
        <div class="text-muted"><span class="dot g-{{ $accession->plant_group_id % 6 }} me-1"></span>{{ $accession->plantGroup->name }}
            <span class="ms-2">Dicatat {{ $accession->created_at->diffForHumans() }}</span></div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('accessions.edit', $accession) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Ubah</a>
        <button type="button" class="btn btn-outline-danger" data-delete
                data-action="{{ route('accessions.destroy', $accession) }}"
                data-name="{{ $accession->name }} ({{ $accession->code }})"><i class="bi bi-trash me-1"></i>Hapus</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <section class="panel p-4 h-100">
            <h2 class="h5 mb-3"><i class="bi bi-geo-alt me-2"></i>Asal pengumpulan</h2>
            @if ($accession->origin)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Provinsi</dt><dd class="col-sm-8">{{ $accession->origin->province }}</dd>
                    <dt class="col-sm-4">Kabupaten/Kota</dt><dd class="col-sm-8">{{ $accession->origin->regency }}</dd>
                    <dt class="col-sm-4">Desa/Kecamatan</dt><dd class="col-sm-8">{{ $accession->origin->village ?? '-' }}</dd>
                    <dt class="col-sm-4">Ketinggian</dt><dd class="col-sm-8">{{ $accession->origin->altitude !== null ? $accession->origin->altitude . ' mdpl' : '-' }}</dd>
                    <dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">{{ $accession->origin->collected_at->translatedFormat('d F Y') }}</dd>
                    <dt class="col-sm-4">Pengumpul</dt><dd class="col-sm-8">{{ $accession->origin->collector ?? '-' }}</dd>
                    <dt class="col-sm-4">Catatan</dt><dd class="col-sm-8 mb-0">{{ $accession->origin->notes ?? '-' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Detail asal belum diisi.</p>
            @endif
        </section>
    </div>
    <div class="col-lg-5">
        <section class="panel p-4 h-100">
            <h2 class="h5 mb-3"><i class="bi bi-snow2 me-2"></i>Perlakuan penyimpanan</h2>
            @forelse ($accession->treatments as $t)
                <div class="mb-3">
                    <span class="chip">{{ $t->name }}</span>
                    <div class="small text-muted">{{ $t->description }}</div>
                </div>
            @empty
                <p class="text-muted mb-0">Belum ada perlakuan dipilih.</p>
            @endforelse
        </section>
    </div>
</div>

@if ($related->isNotEmpty())
    <section class="mt-4">
        <h2 class="h5 mb-3">Aksesi lain di kelompok {{ $accession->plantGroup->name }}</h2>
        <div class="row g-3">
            @foreach ($related as $r)
                <div class="col-md-6 col-xl-3">
                    <article class="panel acc-card g-{{ $r->plant_group_id % 6 }}">
                        <span class="spec-tag align-self-start">{{ $r->code }}</span>
                        <h3><a href="{{ route('accessions.show', $r) }}">{{ $r->name }}</a></h3>
                        <div class="meta"><i class="bi bi-geo-alt me-1"></i>{{ $r->origin?->regency }}, {{ $r->origin?->province }}</div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
