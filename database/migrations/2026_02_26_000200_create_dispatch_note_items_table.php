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
        Schema::create('dispatch_note_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dispatch_note_id')
                ->constrained('dispatch_notes')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('waybill_snapshot')->nullable();
            $table->decimal('collectable_amount_snapshot', 12, 2)->default(0);
            $table->text('item_remarks')->nullable();

            $table->timestamps();

            $table->unique(['dispatch_note_id', 'order_id']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_note_items');
    }
};

