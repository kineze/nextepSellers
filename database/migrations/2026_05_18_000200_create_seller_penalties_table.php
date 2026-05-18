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
        Schema::create('seller_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('penalty_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('delivery_score')->default(100);
            $table->json('effective_areas');
            $table->json('rules')->nullable();
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('applied_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['seller_id', 'penalty_type_id', 'order_id'], 'seller_penalties_unique_trigger');
            $table->index(['seller_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_penalties');
    }
};
