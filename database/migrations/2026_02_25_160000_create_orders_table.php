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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Core
            $table->dateTime('order_datetime')->index();

            $table->enum('status', [
                'draft',
                'approved',
                'confirmed',
                'packed',
                'shipped',
                'completed',
                'cancelled',
            ])->default('draft')->index();

            $table->boolean('is_draft')->default(false)->index(); // optional; you can remove if status=draft is enough

            // Money (use integer cents if you can; using decimal here since you have LKR etc.)
            $table->decimal('net_total', 12, 2)->default(0);
            $table->decimal('total_collectable_amount', 12, 2)->default(0);
            $table->decimal('delivery_charge', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);

            // Shipping / logistics
            $table->string('waybill_no')->nullable()->index();
            $table->dateTime('packed_at')->nullable()->index();
            $table->dateTime('shipped_at')->nullable()->index();
            $table->dateTime('completed_at')->nullable()->index();
            $table->dateTime('cancelled_at')->nullable()->index();

            $table->boolean('is_damaged')->default(false)->index();

            // Delivery status as string (as requested)
            $table->string('delivery_status', 50)->nullable()->index();

            // Payment
            $table->string('payment_status', 30)->nullable()->index();

            // Multi-seller
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();

            // Customer link (optional) + snapshot (always)
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable()->index();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['seller_id', 'order_datetime']);
            $table->index(['seller_id', 'status', 'order_datetime']);
            $table->index(['seller_id', 'customer_id', 'order_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
