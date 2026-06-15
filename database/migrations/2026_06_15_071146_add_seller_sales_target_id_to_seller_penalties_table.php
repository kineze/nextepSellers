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
        Schema::table('seller_penalties', function (Blueprint $table) {
            $table->foreignId('seller_sales_target_id')
                ->nullable()
                ->after('order_id')
                ->constrained()
                ->nullOnDelete();

            $table->unique(
                ['seller_id', 'penalty_type_id', 'seller_sales_target_id'],
                'seller_penalties_unique_sales_target'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_penalties', function (Blueprint $table) {
            $table->dropUnique('seller_penalties_unique_sales_target');
            $table->dropConstrainedForeignId('seller_sales_target_id');
        });
    }
};
