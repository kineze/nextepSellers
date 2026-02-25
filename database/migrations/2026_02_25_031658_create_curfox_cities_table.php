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
        Schema::create('curfox_cities', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable()->index(); // CT-0001 etc.
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('default_warehouse_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('state_id')->references('id')->on('curfox_states')->onDelete('cascade');

            $table->unsignedBigInteger('system_city_id')->nullable();

            $table->foreign('system_city_id')
                ->references('id')
                ->on('cities')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curfox_cities');
    }
};
