<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name_en', 150);
            $table->string('name_si', 150)->nullable();
            $table->string('name_ta', 150)->nullable();
            $table->timestamps();

            $table->index('province_id');
            // Ensure no duplicate district names within the same province (adjust if you prefer global unique):
            $table->unique(['province_id', 'name_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
