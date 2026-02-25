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
        Schema::create('royal_express_logins', function (Blueprint $table) {
            $table->id();

            $table->string('partner_name', 100);
            $table->unsignedBigInteger('partner_user_id')->nullable();
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->unsignedBigInteger('merchant_business_id')->nullable();
            $table->string('email')->nullable()->index();
            $table->text('token')->nullable();
            $table->dateTime('token_expiry')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('role_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('city')->nullable();
            $table->string('state')->nullable();

            $table->timestamps();

            $table->unique('partner_name');
            $table->index(['is_active', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('royal_express_logins');
    }
};
