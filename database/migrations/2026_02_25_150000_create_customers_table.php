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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();

            // A stable grouping key per seller (recommended: "phone:+9477xxxxxxx")
            $table->string('customer_key', 80);

            $table->string('primary_phone', 30);
            $table->string('additional_phone', 30)->nullable();
            $table->string('email')->nullable()->index();

            // Optional defaults for convenience (orders still store snapshot fields)
            $table->string('default_name')->nullable();
            $table->text('default_address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();

            // CRM / risk flags
            $table->string('status', 20)->default('active'); // active|blocked
            $table->string('blocked_reason')->nullable();
            $table->text('notes')->nullable();

            // light analytics
            $table->dateTime('last_order_at')->nullable();
            $table->unsignedInteger('orders_count')->default(0);

            $table->timestamps();

            $table->unique(['seller_id', 'customer_key']);
            $table->index(['seller_id', 'primary_phone']);
            $table->index(['seller_id', 'last_order_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
