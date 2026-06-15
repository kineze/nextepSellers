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
        Schema::table('penalty_types', function (Blueprint $table) {
            $table->enum('trigger_type', ['delivery_score', 'sales_target'])
                ->default('delivery_score')
                ->after('description')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalty_types', function (Blueprint $table) {
            $table->dropColumn('trigger_type');
        });
    }
};
