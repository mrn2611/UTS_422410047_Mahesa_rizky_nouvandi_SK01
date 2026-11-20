<?php

namespace App\Services;

use App\Models\Accession;
use App\Models\CollectionOrigin;
use App\Models\PlantGroup;
use App\Models\StorageTreatment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Agregasi data untuk dasbor. Hanya membaca tabel yang sudah ada (tidak menambah tabel baru).
 */
class AccessionStatisticsService
{
    /** Kelas ketinggian: kunci => label. Urutan array = urutan tampil di grafik. */
    private const ALTITUDE_CLASSES = [
        'a' => '0-200 mdpl',
        'b' => '201-500 mdpl',
        'c' => '501-1000 mdpl',
        'd' => '> 1000 mdpl',
        'n' => 'Tanpa data',
    ];

    public function summary(): array
    {
        return [
            'total'      => Accession::count(),
            'groups'     => PlantGroup::count(),
            'provinces'  => CollectionOrigin::distinct()->count('province'),
            'treatments' => DB::table('accession_storage_treatment')->distinct()->count('storage_treatment_id'),
        ];
    }

    /** Seluruh data grafik dalam satu array siap di-JSON-kan ke Chart.js. */
    public function charts(): array
    {
        return [
            'group'     => $this->perGroup(),
            'province'  => $this->perProvince(),
            'treatment' => $this->perTreatment(),
            'month'     => $this->perMonth(),
            'altitude'  => $this->perAltitude(),
        ];
    }

    public function latest(int $limit = 5): Collection
    {
        return Accession::with(['plantGroup', 'origin'])->latest('id')->limit($limit)->get();
    }

    /** Jumlah aksesi per kelompok tanaman. */
    private function perGroup(): array
    {
        $rows = PlantGroup::withCount('accessions')
            ->orderByDesc('accessions_count')->orderBy('name')->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'values' => $rows->pluck('accessions_count')->map(fn ($n) => (int) $n)->all(),
            'ids'    => $rows->pluck('id')->all(),
        ];
    }

    /** 8 provinsi asal dengan aksesi terbanyak. */
    private function perProvince(): array
    {
        $rows = CollectionOrigin::select('province')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('province')
            ->orderByDesc('total')->orderBy('province')
            ->limit(8)->get();

        return [
            'labels' => $rows->pluck('province')->all(),
            'values' => $rows->pluck('total')->map(fn ($n) => (int) $n)->all(),
        ];
    }

    /** Jumlah aksesi per perlakuan penyimpanan (lewat tabel pivot). */
    private function perTreatment(): array
    {
        $rows = StorageTreatment::withCount('accessions')
            ->orderByDesc('accessions_count')->orderBy('name')->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'values' => $rows->pluck('accessions_count')->map(fn ($n) => (int) $n)->all(),
            'ids'    => $rows->pluck('id')->all(),
        ];
    }

    /** Jumlah aksesi yang dikumpulkan per bulan; bulan tanpa data diisi 0 agar garis tidak melompat. */
    private function perMonth(): array
    {
        $driver = DB::connection()->getDriverName();
        $dateExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m', collected_at)",
            'pgsql'  => "to_char(collected_at, 'YYYY-MM')",
            default  => "DATE_FORMAT(collected_at, '%Y-%m')",
        };

        $rows = CollectionOrigin::whereNotNull('collected_at')
            ->selectRaw("{$dateExpr} as ym, COUNT(*) as total")
            ->groupBy('ym')->orderBy('ym')
            ->pluck('total', 'ym');

        if ($rows->isEmpty()) {
            return ['labels' => [], 'values' => []];
        }

        try {
            $cursor = Carbon::createFromFormat('Y-m-d', $rows->keys()->first() . '-01')->startOfMonth();
            $end    = Carbon::createFromFormat('Y-m-d', $rows->keys()->last() . '-01')->startOfMonth();
        } catch (\Throwable $e) {
            return [
                'labels' => $rows->keys()->all(),
                'values' => $rows->values()->map(fn ($n) => (int) $n)->all(),
            ];
        }

        $labels = [];
        $values = [];
        while ($cursor <= $end) {
            $labels[] = $cursor->translatedFormat('M Y');
            $values[] = (int) ($rows[$cursor->format('Y-m')] ?? 0);
            $cursor->addMonth();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /** Sebaran aksesi menurut kelas ketinggian lokasi pengumpulan. */
    private function perAltitude(): array
    {
        $counts = CollectionOrigin::selectRaw("
                CASE
                    WHEN altitude IS NULL THEN 'n'
                    WHEN altitude <= 200  THEN 'a'
                    WHEN altitude <= 500  THEN 'b'
                    WHEN altitude <= 1000 THEN 'c'
                    ELSE 'd'
                END as kelas, COUNT(*) as total")
            ->groupBy('kelas')
            ->pluck('total', 'kelas');

        $labels = [];
        $values = [];
        foreach (self::ALTITUDE_CLASSES as $key => $label) {
            $labels[] = $label;
            $values[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
