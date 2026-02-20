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
        Schema::table('business_informations', function (Blueprint $table) {
            $table->unique('seller_id', 'business_informations_seller_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_informations', function (Blueprint $table) {
            $table->dropUnique('business_informations_seller_id_unique');
        });
    }
};
