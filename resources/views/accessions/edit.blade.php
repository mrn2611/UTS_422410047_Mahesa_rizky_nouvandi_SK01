@extends('layouts.app')

@section('title', 'Ubah Aksesi - Bank Benih Pusaka')

@section('content')
<div class="page-head">
    <div>
        <h1 class="mb-0">Ubah aksesi</h1>
        <p>Perbarui data <span class="spec-tag">{{ $accession->code }}</span></p>
    </div>
</div>
<form id="accession-form" action="{{ route('accessions.update', $accession) }}" method="POST" novalidate>
    @csrf
    @method('PUT')
    @include('accessions._form')
</form>
@endsection
