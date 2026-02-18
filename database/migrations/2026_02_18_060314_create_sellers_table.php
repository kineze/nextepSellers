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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();

            // Basic Details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            // Seller Type
            $table->enum('seller_type', ['individual', 'business']);

            // Tax & Identity
            $table->string('tax_number')->nullable();
            $table->string('nic_number')->nullable();
            $table->string('nic_front')->nullable(); // file path
            $table->string('nic_back')->nullable();  // file path

            // Status Workflow
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'blocked'
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            // Verification Flags
            $table->boolean('email_verified')->default(false);
            $table->boolean('phone_verified')->default(false);

            // Link to users table after approval
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
