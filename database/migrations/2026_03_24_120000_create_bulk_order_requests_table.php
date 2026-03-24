<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_order_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 40)->unique();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted', 'approved', 'cancelled'])->default('draft')->index();
            $table->unsignedInteger('orders_count')->default(0);
            $table->timestamps();

            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_order_requests');
    }
};
