<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_origins', function (Blueprint $table) {
            $table->id();
            // unique() menjamin relasi ONE-TO-ONE: satu aksesi hanya punya satu detail asal
            $table->foreignId('accession_id')->unique()->constrained('accessions')->cascadeOnDelete();
            $table->string('province', 100);
            $table->string('regency', 100);
            $table->string('village', 100)->nullable();
            $table->unsignedSmallInteger('altitude')->nullable();   // mdpl
            $table->date('collected_at');
            $table->string('collector', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('province');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_origins');
    }
};
