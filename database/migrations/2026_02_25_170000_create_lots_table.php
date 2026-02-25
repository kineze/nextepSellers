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
        Schema::create('lots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('variant_id')
                ->constrained('varients')
                ->cascadeOnDelete();

            $table->string('lot_number', 50)->unique();
            $table->string('barcode', 120)->nullable()->unique();
            $table->date('manufactured_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['variant_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};

