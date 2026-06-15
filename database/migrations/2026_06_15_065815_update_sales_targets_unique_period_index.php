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
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropUnique('sales_targets_unique_period');
            $table->unique(['year', 'frequency', 'period_number'], 'sales_targets_unique_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropUnique('sales_targets_unique_period');
            $table->unique(['year', 'frequency', 'target_type', 'period_number'], 'sales_targets_unique_period');
        });
    }
};
