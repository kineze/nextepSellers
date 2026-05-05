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
        if (Schema::hasColumn('label_settings', 'is_active')) {
            return;
        }

        Schema::table('label_settings', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('label_settings', 'is_active')) {
            return;
        }

        Schema::table('label_settings', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
