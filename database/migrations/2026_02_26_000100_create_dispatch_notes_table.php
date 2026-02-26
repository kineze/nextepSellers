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
        Schema::create('dispatch_notes', function (Blueprint $table) {
            $table->id();

            $table->string('ref_no', 80)->unique();
            $table->date('dispatch_date')->index();
            $table->time('dispatch_time')->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('net_total', 12, 2)->default(0);
            $table->decimal('delivery_total', 12, 2)->default(0);
            $table->decimal('total_collectable', 12, 2)->default(0);

            $table->string('status', 30)->default('draft')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_notes');
    }
};

