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
        Schema::create('penalty_type_sales_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_target_id')->constrained()->cascadeOnDelete();
            $table->foreignId('penalty_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sales_target_id', 'penalty_type_id'], 'sales_target_penalty_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_type_sales_target');
    }
};
