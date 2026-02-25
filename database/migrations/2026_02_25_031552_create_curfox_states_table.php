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
        Schema::create('curfox_states', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable()->index(); // ST-0001 etc.
            $table->string('name');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->boolean('has_child')->default(false);
            $table->timestamps();

            $table->unsignedBigInteger('system_district_id')->nullable();

            $table->foreign('system_district_id')
                ->references('id')
                ->on('districts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curfox_states');
    }
};
