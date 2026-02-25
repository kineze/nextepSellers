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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('name_en', 150);
            $table->string('name_si', 150)->nullable();
            $table->string('name_ta', 150)->nullable();

            $table->string('sub_name_en', 150)->nullable();
            $table->string('sub_name_si', 150)->nullable();
            $table->string('sub_name_ta', 150)->nullable();

            $table->string('postcode', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();

            $table->index('district_id');
            $table->index('postcode');
            $table->index(['latitude', 'longitude']);

            $table->unique(['district_id', 'name_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
