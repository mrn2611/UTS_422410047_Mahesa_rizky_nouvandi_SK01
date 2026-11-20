<?php

namespace Database\Seeders;

use App\Models\Accession;
use App\Models\PlantGroup;
use App\Models\StorageTreatment;
use App\Services\AccessionService;
use Illuminate\Database\Seeder;

class AccessionSeeder extends Seeder
{
    public function run(AccessionService $service): void
    {
        $group = PlantGroup::pluck('id', 'name');
        $treat = StorageTreatment::pluck('id', 'name');

        $rows = [
            ['BBP-0001', 'Padi Pandanwangi', 'Serealia', 'Jawa Barat', 'Cianjur', 'Warungkondang', 450, '2024-03-12', 'Asep Rahmat',
                ['Pengeringan Matahari', 'Silika Gel']],
            ['BBP-0002', 'Jagung Pulut Madura', 'Serealia', 'Jawa Timur', 'Sumenep', 'Gapura', 25, '2024-04-02', 'Siti Aminah',
                ['Pengeringan Matahari', 'Cold Storage (4°C)']],
            ['BBP-0003', 'Kedelai Grobogan', 'Kacang-kacangan', 'Jawa Tengah', 'Grobogan', 'Purwodadi', 60, '2024-05-20', 'Budi Santoso',
                ['Silika Gel', 'Kemasan Vakum', 'Freezer (-20°C)']],
            ['BBP-0004', 'Kacang Tunggak Lokal Gunungkidul', 'Kacang-kacangan', 'DI Yogyakarta', 'Gunungkidul', 'Playen', 180, '2024-06-08', 'Dewi Lestari',
                ['Kemasan Vakum']],
            ['BBP-0005', 'Ubi Cilembu', 'Umbi-umbian', 'Jawa Barat', 'Sumedang', 'Cilembu', 700, '2024-07-14', 'Asep Rahmat',
                ['Ruang Sejuk (15°C)']],
            ['BBP-0006', 'Cabai Rawit Hiyung', 'Sayuran', 'Kalimantan Selatan', 'Tapin', 'Hiyung', 15, '2024-08-01', 'Rahmat Hidayat',
                ['Pengeringan Matahari', 'Silika Gel', 'Cold Storage (4°C)']],
            ['BBP-0007', 'Terung Ungu Kopek', 'Sayuran', 'Sumatera Barat', 'Agam', 'Baso', 900, '2024-09-17', 'Yuliana Putri',
                ['Silika Gel']],
            ['BBP-0008', 'Kunyit Lokal Toraja', 'Rempah & Obat', 'Sulawesi Selatan', 'Toraja Utara', 'Rantepao', 800, '2024-10-05', 'Yohanis Tandi',
                ['Ruang Sejuk (15°C)', 'Kemasan Vakum']],
        ];

        foreach ($rows as [$code, $name, $grp, $prov, $reg, $vil, $alt, $date, $collector, $treatNames]) {
            if (Accession::where('code', $code)->exists()) {
                continue;
            }

            $service->create([
                'code'           => $code,
                'name'           => $name,
                'plant_group_id' => $group[$grp],
                'origin'         => [
                    'province' => $prov, 'regency' => $reg, 'village' => $vil,
                    'altitude' => $alt, 'collected_at' => $date, 'collector' => $collector,
                ],
                'treatments'     => collect($treatNames)->map(fn ($n) => $treat[$n])->all(),
            ]);
        }
    }
}
