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
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grn_id')->constrained('grns')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('varients')->cascadeOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();

            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);

            $table->string('lot_number', 50)->nullable();
            $table->string('barcode')->nullable();
            $table->date('manufactured_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->timestamps();

            $table->index(['grn_id', 'variant_id']);
            $table->index(['lot_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};

