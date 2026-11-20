<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Kode aksesi selalu huruf besar & tanpa spasi di pinggir
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'code'           => [
                'required', 'string', 'max:30', 'regex:/^[A-Z0-9\-]+$/',
                // ignore() dipakai saat update agar kode miliknya sendiri tidak dianggap duplikat
                Rule::unique('accessions', 'code')->ignore($this->route('accession')),
            ],
            'name'           => ['required', 'string', 'max:150'],
            'plant_group_id' => ['required', 'integer', 'exists:plant_groups,id'],

            'origin'              => ['required', 'array'],
            'origin.province'     => ['required', 'string', 'max:100'],
            'origin.regency'      => ['required', 'string', 'max:100'],
            'origin.village'      => ['nullable', 'string', 'max:100'],
            'origin.altitude'     => ['nullable', 'integer', 'min:0', 'max:5000'],
            'origin.collected_at' => ['required', 'date', 'before_or_equal:today'],
            'origin.collector'    => ['nullable', 'string', 'max:100'],
            'origin.notes'        => ['nullable', 'string', 'max:1000'],

            'treatments'   => ['nullable', 'array'],
            'treatments.*' => ['integer', 'distinct', 'exists:storage_treatments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'             => ':attribute wajib diisi.',
            'unique'               => ':attribute sudah digunakan aksesi lain.',
            'max'                  => ':attribute maksimal :max karakter.',
            'integer'              => ':attribute harus berupa angka bulat.',
            'exists'               => ':attribute yang dipilih tidak valid.',
            'date'                 => ':attribute bukan tanggal yang valid.',
            'before_or_equal'      => ':attribute tidak boleh melebihi hari ini.',
            'code.regex'           => 'Kode aksesi hanya boleh huruf kapital, angka, dan tanda hubung (contoh: BBP-0001).',
            'origin.altitude.min'  => 'Ketinggian tidak boleh negatif.',
            'origin.altitude.max'  => 'Ketinggian maksimal 5000 mdpl.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code'                => 'Kode aksesi',
            'name'                => 'Nama aksesi',
            'plant_group_id'      => 'Kelompok tanaman',
            'origin.province'     => 'Provinsi',
            'origin.regency'      => 'Kabupaten/Kota',
            'origin.village'      => 'Desa',
            'origin.altitude'     => 'Ketinggian',
            'origin.collected_at' => 'Tanggal pengumpulan',
            'origin.collector'    => 'Pengumpul',
            'origin.notes'        => 'Catatan',
            'treatments'          => 'Perlakuan penyimpanan',
            'treatments.*'        => 'Perlakuan penyimpanan',
        ];
    }
}
