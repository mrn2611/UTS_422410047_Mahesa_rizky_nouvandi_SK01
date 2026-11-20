<?php

namespace Database\Seeders;

use App\Models\StorageTreatment;
use Illuminate\Database\Seeder;

class StorageTreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $treatments = [
            ['Pengeringan Matahari', 'Benih dijemur hingga kadar air aman'],
            ['Silika Gel', 'Penyimpanan dengan penyerap kelembapan'],
            ['Kemasan Vakum', 'Kemasan kedap udara'],
            ['Ruang Sejuk (15°C)', 'Penyimpanan jangka menengah'],
            ['Cold Storage (4°C)', 'Penyimpanan jangka menengah-panjang'],
            ['Freezer (-20°C)', 'Penyimpanan jangka panjang'],
        ];

        foreach ($treatments as [$name, $description]) {
            StorageTreatment::updateOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
