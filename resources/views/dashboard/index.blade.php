@extends('layouts.app')

@section('title', 'Dasbor - Bank Benih Pusaka')

@section('content')
<div class="page-head align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="mb-0">Dasbor Koleksi</h1>
            <span class="live-pill"><span class="pulse-dot me-1"></span>Aktif</span>
        </div>
        <p class="text-muted mb-0">Visualisasi data & ringkasan grafis koleksi benih. Klik elemen grafik untuk memfilter daftar aksesi secara interaktif.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('accessions.create') }}" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Aksesi</a>
        <a href="{{ route('accessions.index') }}" class="btn btn-outline-primary"><i class="bi bi-list-ul me-1"></i>Lihat Semua</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="{{ route('accessions.index') }}" class="stat-card stat-emerald text-decoration-none">
            <div class="stat-icon-wrap">
                <i class="bi bi-seedling"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num" data-count="{{ $summary['total'] }}">0</div>
                <div class="stat-label">Aksesi Tersimpan</div>
            </div>
            <div class="stat-arrow"><i class="bi bi-arrow-right"></i></div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('accessions.index') }}" class="stat-card stat-indigo text-decoration-none">
            <div class="stat-icon-wrap">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num" data-count="{{ $summary['groups'] }}">0</div>
                <div class="stat-label">Kelompok Tanaman</div>
            </div>
            <div class="stat-arrow"><i class="bi bi-arrow-right"></i></div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('accessions.index') }}" class="stat-card stat-amber text-decoration-none">
            <div class="stat-icon-wrap">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num" data-count="{{ $summary['provinces'] }}">0</div>
                <div class="stat-label">Provinsi Asal</div>
            </div>
            <div class="stat-arrow"><i class="bi bi-arrow-right"></i></div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('accessions.index') }}" class="stat-card stat-teal text-decoration-none">
            <div class="stat-icon-wrap">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num" data-count="{{ $summary['treatments'] }}">0</div>
                <div class="stat-label">Perlakuan Terpakai</div>
            </div>
            <div class="stat-arrow"><i class="bi bi-arrow-right"></i></div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <section class="panel chart-panel p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h2 class="chart-title"><i class="bi bi-pie-chart-fill text-emerald me-2"></i>Aksesi per Kelompok</h2>
                    <p class="chart-sub mb-0">Klik irisan untuk menyaring daftar aksesi</p>
                </div>
                <span class="chart-hint-badge"><i class="bi bi-cursor-fill me-1"></i>Filter</span>
            </div>
            <div class="chart-box tall"><canvas id="chart-group" role="img" aria-label="Diagram donat jumlah aksesi per kelompok tanaman"></canvas></div>
        </section>
    </div>
    <div class="col-lg-7">
        <section class="panel chart-panel p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h2 class="chart-title"><i class="bi bi-bar-chart-line-fill text-forest me-2"></i>Provinsi Asal Terbanyak</h2>
                    <p class="chart-sub mb-0">Delapan provinsi dengan koleksi terbanyak</p>
                </div>
                <span class="chart-hint-badge"><i class="bi bi-cursor-fill me-1"></i>Filter</span>
            </div>
            <div class="chart-box tall"><canvas id="chart-province" role="img" aria-label="Grafik batang jumlah aksesi per provinsi asal"></canvas></div>
        </section>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <section class="panel chart-panel p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h2 class="chart-title"><i class="bi bi-sliders2-vertical text-amber me-2"></i>Perlakuan Penyimpanan</h2>
                    <p class="chart-sub mb-0">Distribusi perlakuan penyimpanan benih</p>
                </div>
                <span class="chart-hint-badge"><i class="bi bi-cursor-fill me-1"></i>Filter</span>
            </div>
            <div class="chart-box"><canvas id="chart-treatment" role="img" aria-label="Grafik batang jumlah aksesi per perlakuan penyimpanan"></canvas></div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="panel chart-panel p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h2 class="chart-title"><i class="bi bi-water text-cyan me-2"></i>Ketinggian Lokasi Pengumpulan</h2>
                    <p class="chart-sub mb-0">Sebaran aksesi berdasarkan kelas mdpl</p>
                </div>
                <span class="chart-tag-badge">Elevasi</span>
            </div>
            <div class="chart-box"><canvas id="chart-altitude" role="img" aria-label="Grafik batang sebaran aksesi menurut kelas ketinggian"></canvas></div>
        </section>
    </div>
</div>

<section class="panel chart-panel p-4 mb-4">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h2 class="chart-title"><i class="bi bi-graph-up-arrow text-emerald me-2"></i>Tren Pengumpulan per Bulan</h2>
            <p class="chart-sub mb-0">Pertumbuhan dan dinamika jumlah koleksi berdasarkan tanggal pengumpulan</p>
        </div>
        <span class="chart-tag-badge"><i class="bi bi-calendar3 me-1"></i>Bulanan</span>
    </div>
    <div class="chart-box"><canvas id="chart-month" role="img" aria-label="Grafik garis jumlah aksesi yang dikumpulkan per bulan"></canvas></div>
</section>

<section class="panel latest-panel">
    <div class="p-4 pb-3 d-flex justify-content-between align-items-center border-bottom">
        <div>
            <h2 class="chart-title mb-1"><i class="bi bi-clock-history text-forest me-2"></i>Aksesi Terbaru</h2>
            <p class="text-muted small mb-0">Spesimen benih pusaka yang baru saja didaftarkan ke sistem</p>
        </div>
        <a href="{{ route('accessions.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right-circle me-1"></i>Lihat semua aksesi</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 dashboard-table">
            <thead>
                <tr>
                    <th class="ps-4">Kode Aksesi</th>
                    <th>Nama Aksesi</th>
                    <th>Kelompok Tanaman</th>
                    <th>Asal Pengumpulan</th>
                    <th class="text-end pe-4">Dicatat</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($latest as $a)
                <tr class="dashboard-row">
                    <td class="ps-4">
                        <span class="spec-tag">{{ $a->code }}</span>
                    </td>
                    <td>
                        <a href="{{ route('accessions.show', $a) }}" class="name-link">
                            <span class="fw-semibold">{{ $a->name }}</span>
                            <i class="bi bi-box-arrow-up-right ms-1 text-muted fs-xs"></i>
                        </a>
                    </td>
                    <td class="text-nowrap">
                        <span class="group-pill g-bg-{{ $a->plant_group_id % 6 }}">
                            <span class="dot g-{{ $a->plant_group_id % 6 }} me-1"></span>{{ $a->plantGroup->name }}
                        </span>
                    </td>
                    <td>
                        <span class="text-dark fw-medium">{{ $a->origin?->regency ?? '-' }}</span>
                        @if($a->origin?->province)
                            <span class="text-muted small">, {{ $a->origin->province }}</span>
                        @endif
                    </td>
                    <td class="text-end pe-4 text-muted text-nowrap">
                        <span class="badge bg-light text-secondary border py-1 px-2">
                            <i class="bi bi-clock me-1"></i>{{ $a->created_at->diffForHumans() }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>Belum ada aksesi tersimpan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Data grafik dikirim sebagai JSON; charts.js yang menggambar --}}
<script type="application/json" id="chart-data" data-index-url="{{ route('accessions.index') }}">@json($charts)</script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/charts.js') }}"></script>
@endpush
