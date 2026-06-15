<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('system_data', function (Blueprint $table) {
            $table->unsignedTinyInteger('year_start_month')->default(1)->after('fax');
            $table->unsignedTinyInteger('year_end_month')->default(12)->after('year_start_month');
        });

        DB::table('system_data')->update([
            'year_start_month' => 1,
            'year_end_month' => 12,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_data', function (Blueprint $table) {
            $table->dropColumn(['year_start_month', 'year_end_month']);
        });
    }
};
