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
        Schema::create('seller_sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_target_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->enum('frequency', ['monthly', 'quarterly', 'yearly']);
            $table->enum('target_type', ['qty', 'sales_amount']);
            $table->unsignedTinyInteger('period_number');
            $table->string('period_label', 100);
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->decimal('target_value', 15, 2);
            $table->decimal('actual_value', 15, 2)->default(0);
            $table->decimal('achievement_percentage', 8, 2)->default(0);
            $table->boolean('is_achieved')->default(false)->index();
            $table->boolean('penalty_applied')->default(false)->index();
            $table->timestamp('assessed_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['seller_id', 'sales_target_id'], 'seller_sales_targets_unique_target');
            $table->index(['seller_id', 'year', 'frequency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_sales_targets');
    }
};
