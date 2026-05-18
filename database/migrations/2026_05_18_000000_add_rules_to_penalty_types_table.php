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
        if (! Schema::hasColumn('penalty_types', 'rules')) {
            Schema::table('penalty_types', function (Blueprint $table) {
                $table->json('rules')->nullable()->after('effective_areas');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('penalty_types', 'rules')) {
            Schema::table('penalty_types', function (Blueprint $table) {
                $table->dropColumn('rules');
            });
        }
    }
};
