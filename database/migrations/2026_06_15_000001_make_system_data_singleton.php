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
        $latestId = DB::table('system_data')->latest('id')->value('id');

        if ($latestId) {
            DB::table('system_data')->where('id', '!=', $latestId)->delete();
        }

        Schema::table('system_data', function (Blueprint $table) {
            $table->string('singleton_key')->default('system')->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_data', function (Blueprint $table) {
            $table->dropUnique(['singleton_key']);
            $table->dropColumn('singleton_key');
        });
    }
};
