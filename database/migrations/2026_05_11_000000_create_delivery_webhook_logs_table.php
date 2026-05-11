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
        Schema::create('delivery_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('waybill_no')->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->string('status_key')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->timestamps();

            $table->index(['waybill_no', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_webhook_logs');
    }
};
