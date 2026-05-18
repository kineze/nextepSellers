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
        if (! Schema::hasColumn('penalty_types', 'effective_areas')) {
            Schema::table('penalty_types', function (Blueprint $table) {
                $table->json('effective_areas')->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('penalty_types', 'effective_areas')) {
            Schema::table('penalty_types', function (Blueprint $table) {
                $table->dropColumn('effective_areas');
            });
        }
    }
};
