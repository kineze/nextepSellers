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
        Schema::table('sellers', function (Blueprint $table) {
            $table->foreignId('seller_level_id')
                ->nullable()
                ->after('user_id')
                ->constrained('levels')
                ->nullOnDelete();

            $table->unsignedInteger('points')
                ->default(0)
                ->after('seller_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_level_id');
            $table->dropColumn('points');
        });
    }
};
