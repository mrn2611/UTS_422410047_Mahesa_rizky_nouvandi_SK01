<?php

namespace Database\Seeders;

use App\Models\PlantGroup;
use Illuminate\Database\Seeder;

class PlantGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['Serealia', 'Padi, jagung, sorgum, dan sejenisnya'],
            ['Kacang-kacangan', 'Kedelai, kacang tanah, kacang tunggak, dan sejenisnya'],
            ['Umbi-umbian', 'Ubi jalar, singkong, talas, dan sejenisnya'],
            ['Sayuran', 'Cabai, terung, tomat, dan sayuran lain'],
            ['Buah-buahan', 'Buah semusim maupun tahunan'],
            ['Rempah & Obat', 'Tanaman rempah dan tanaman obat'],
        ];

        foreach ($groups as [$name, $description]) {
            PlantGroup::updateOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
