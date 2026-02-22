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
        Schema::table('attributes', function (Blueprint $table) {
            if (Schema::hasColumn('attributes', 'color_name')) {
                $table->dropColumn('color_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            if (!Schema::hasColumn('attributes', 'color_name')) {
                $table->string('color_name')->nullable()->after('values');
            }
        });
    }
};
