<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel pivot untuk relasi MANY-TO-MANY aksesi <-> perlakuan penyimpanan
        Schema::create('accession_storage_treatment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accession_id')->constrained('accessions')->cascadeOnDelete();
            $table->foreignId('storage_treatment_id')->constrained('storage_treatments')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['accession_id', 'storage_treatment_id'], 'acc_treatment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accession_storage_treatment');
    }
};
