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
          Schema::create('business_informations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Business Details
            $table->string('business_name');
            $table->string('business_registration_number')->nullable();
            $table->string('business_type')->nullable();
            $table->date('business_registered_date')->nullable();

            // Address
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Sri Lanka');

            // Documents
            $table->string('business_registration_document')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_informations');
    }
};
