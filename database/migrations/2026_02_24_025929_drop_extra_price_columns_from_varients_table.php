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
        Schema::table('varients', function (Blueprint $table) {
            if (Schema::hasColumn('varients', 'min_price')) {
                $table->dropColumn('min_price');
            }
            if (Schema::hasColumn('varients', 'max_price')) {
                $table->dropColumn('max_price');
            }
            if (Schema::hasColumn('varients', 'cost')) {
                $table->dropColumn('cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('varients', function (Blueprint $table) {
            if (!Schema::hasColumn('varients', 'min_price')) {
                $table->decimal('min_price', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('varients', 'max_price')) {
                $table->decimal('max_price', 12, 2)->nullable()->after('min_price');
            }
            if (!Schema::hasColumn('varients', 'cost')) {
                $table->decimal('cost', 12, 2)->nullable()->after('max_price');
            }
        });
    }
};
