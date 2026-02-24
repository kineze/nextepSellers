<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_product_varients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_product_id')->constrained('supplier_products')->cascadeOnDelete();
            $table->foreignId('varient_id')->constrained('varients')->cascadeOnDelete();
            $table->string('supplier_sku')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->unsignedInteger('lead_days')->nullable();
            $table->unsignedInteger('moq')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['supplier_product_id', 'varient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_product_varients');
    }
};
