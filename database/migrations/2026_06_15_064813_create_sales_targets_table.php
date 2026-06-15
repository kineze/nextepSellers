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
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->enum('frequency', ['monthly', 'quarterly', 'yearly']);
            $table->enum('target_type', ['qty', 'sales_amount']);
            $table->unsignedTinyInteger('period_number');
            $table->string('period_label', 100);
            $table->decimal('target_value', 15, 2);
            $table->timestamps();

            $table->unique(['year', 'frequency', 'period_number'], 'sales_targets_unique_period');
            $table->index(['year', 'frequency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
