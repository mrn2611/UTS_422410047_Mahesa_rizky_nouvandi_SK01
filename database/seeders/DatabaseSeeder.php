<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlantGroupSeeder::class,
            StorageTreatmentSeeder::class,
            AccessionSeeder::class,   // harus terakhir: butuh data master di atas
        ]);
    }
}
