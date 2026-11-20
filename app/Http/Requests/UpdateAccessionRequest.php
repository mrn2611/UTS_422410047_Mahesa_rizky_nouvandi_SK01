<?php

namespace App\Http\Requests;

/**
 * Aturan sama dengan Store. Perbedaan perilaku unik-kode ditangani oleh
 * Rule::unique()->ignore($this->route('accession')) di kelas induk.
 */
class UpdateAccessionRequest extends StoreAccessionRequest
{
}
