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
        if (!Schema::hasTable('delivery_webhook_logs') || !Schema::hasColumn('delivery_webhook_logs', 'raw_data')) {
            return;
        }

        Schema::table('delivery_webhook_logs', function (Blueprint $table) {
            $table->dropColumn('raw_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('delivery_webhook_logs') || Schema::hasColumn('delivery_webhook_logs', 'raw_data')) {
            return;
        }

        Schema::table('delivery_webhook_logs', function (Blueprint $table) {
            $table->json('raw_data')->nullable()->after('waybill_no');
        });
    }
};
