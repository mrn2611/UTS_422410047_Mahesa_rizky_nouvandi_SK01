@extends('layouts.app')

@section('title', 'Tambah Aksesi - Bank Benih Pusaka')

@section('content')
<div class="page-head">
    <div>
        <h1 class="mb-0">Tambah aksesi</h1>
        <p>Isi identitas, asal pengumpulan, dan perlakuan penyimpanan benih.</p>
    </div>
</div>
<form id="accession-form" action="{{ route('accessions.store') }}" method="POST" novalidate>
    @csrf
    @include('accessions._form')
</form>
@endsection
