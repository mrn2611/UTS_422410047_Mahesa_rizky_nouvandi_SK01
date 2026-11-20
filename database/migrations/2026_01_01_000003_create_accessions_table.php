<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();          // kode aksesi, mis. BBP-0001
            $table->string('name', 150);                    // nama aksesi / varietas lokal
            $table->foreignId('plant_group_id')             // relasi 1-N ke kelompok tanaman
                  ->constrained('plant_groups')
                  ->restrictOnDelete();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessions');
    }
};
