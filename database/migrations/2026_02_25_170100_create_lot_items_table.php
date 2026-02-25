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
        Schema::create('lot_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lot_id')
                ->constrained('lots')
                ->cascadeOnDelete();

            $table->foreignId('variant_id')
                ->constrained('varients')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->string('barcode')->unique();

            $table->enum('status', [
                'available',
                'reserved',
                'sold',
                'damaged',
                'returned',
            ])->default('available');

            $table->timestamps();

            $table->index(['lot_id', 'status']);
            $table->index(['variant_id', 'status']);
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_items');
    }
};

