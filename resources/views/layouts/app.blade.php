<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bank Benih Pusaka')</title>
    <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@600;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<header class="topbar py-3 mb-4">
    <div class="container d-flex align-items-center justify-content-between gap-3">
        <a class="brand" href="{{ route('accessions.index') }}">
            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                <path d="M16 3c7 5 10 12 7 19-2 4-6 7-7 7s-5-3-7-7C6 15 9 8 16 3z" fill="#d9a441"/>
                <path d="M16 8v19" stroke="#214534" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            Bank Benih Pusaka
        </a>
        <nav class="topnav" aria-label="Menu utama">
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dasbor</a>
            <a href="{{ route('accessions.index') }}" @class(['active' => request()->routeIs('accessions.*')])>Aksesi</a>
        </nav>
    </div>
</header>

<main class="container pb-5">
    @yield('content')
</main>

{{-- Pesan sukses dari session dikirim ke JS untuk ditampilkan sebagai toast --}}
@if (session('success'))
    <div hidden data-flash="{{ session('success') }}" data-type="success"></div>
@endif
<div id="toast-wrap" aria-live="polite"></div>

{{-- Modal konfirmasi hapus (dipakai bersama oleh semua halaman) --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deleteForm" method="POST" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h2 class="modal-title h5" id="deleteTitle">Hapus aksesi?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1"><strong id="deleteName"></strong> akan dihapus.</p>
                <p class="text-muted mb-0">Detail asal pengumpulan dan perlakuan penyimpanannya ikut terhapus. Tindakan ini tidak bisa dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus aksesi</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
