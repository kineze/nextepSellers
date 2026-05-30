<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lot_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('varients')->cascadeOnDelete();
            $table->string('barcode');
            $table->enum('disposition', ['returned', 'damaged']);
            $table->timestamps();

            $table->unique(['order_return_id', 'barcode']);
            $table->unique(['order_return_id', 'lot_item_id']);
            $table->index(['order_return_id', 'disposition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
