<?php

namespace App\Services;

use App\Models\Accession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan service: seluruh logika bisnis & transaksi database ada di sini,
 * sehingga Controller tetap tipis (hanya menerima request & mengembalikan response).
 */
class AccessionService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Accession::with(['plantGroup', 'origin', 'treatments'])
            ->search($filters['q'] ?? null)
            ->filter($filters)
            ->orderBy($this->sortColumn($filters['sort'] ?? null), $this->sortDirection($filters['dir'] ?? null))
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Whitelist kolom urut agar input pengguna tidak masuk mentah ke query. */
    private function sortColumn(?string $col): string
    {
        return in_array($col, ['code', 'name'], true) ? $col : 'id';
    }

    private function sortDirection(?string $dir): string
    {
        return $dir === 'asc' ? 'asc' : 'desc';
    }

    /** Saran kode aksesi berikutnya, mis. BBP-0009. */
    public function nextCode(): string
    {
        $last = Accession::where('code', 'like', 'BBP-%')->orderByDesc('code')->value('code');
        $number = $last ? ((int) substr($last, 4)) + 1 : 1;

        return sprintf('BBP-%04d', $number);
    }

    /**
     * Simpan aksesi + detail asal + perlakuan dalam SATU transaksi.
     * Jika salah satu langkah gagal, semua perubahan dibatalkan (rollback).
     */
    public function create(array $data): Accession
    {
        return DB::transaction(function () use ($data) {
            $accession = Accession::create([
                'code'           => $data['code'],
                'name'           => $data['name'],
                'plant_group_id' => $data['plant_group_id'],
            ]);

            $accession->origin()->create($data['origin']);
            $accession->treatments()->sync($data['treatments'] ?? []);

            return $accession;
        });
    }

    public function update(Accession $accession, array $data): Accession
    {
        return DB::transaction(function () use ($accession, $data) {
            $accession->update([
                'code'           => $data['code'],
                'name'           => $data['name'],
                'plant_group_id' => $data['plant_group_id'],
            ]);

            $accession->origin()->updateOrCreate(
                ['accession_id' => $accession->id],
                $data['origin']
            );

            // sync(): tambah yang baru, hapus yang tidak dipilih lagi
            $accession->treatments()->sync($data['treatments'] ?? []);

            return $accession;
        });
    }

    public function delete(Accession $accession): void
    {
        DB::transaction(function () use ($accession) {
            $accession->treatments()->detach();
            $accession->origin()->delete();
            $accession->delete();
        });
    }
}
