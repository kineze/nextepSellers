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
        if (!Schema::hasTable('delivery_webhook_logs')) {
            return;
        }

        Schema::table('delivery_webhook_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_webhook_logs', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('delivery_webhook_logs', 'is_matched')) {
                $table->boolean('is_matched')->default(false)->after('status')->index();
            }

            if (!Schema::hasColumn('delivery_webhook_logs', 'processed_result')) {
                $table->json('processed_result')->nullable()->after('is_matched');
            }

            if (!Schema::hasColumn('delivery_webhook_logs', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_result')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('delivery_webhook_logs')) {
            return;
        }

        Schema::table('delivery_webhook_logs', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_webhook_logs', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }

            if (Schema::hasColumn('delivery_webhook_logs', 'processed_at')) {
                $table->dropColumn('processed_at');
            }

            if (Schema::hasColumn('delivery_webhook_logs', 'processed_result')) {
                $table->dropColumn('processed_result');
            }

            if (Schema::hasColumn('delivery_webhook_logs', 'is_matched')) {
                $table->dropColumn('is_matched');
            }
        });
    }
};
