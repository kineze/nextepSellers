<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('supplier_product_code')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->unsignedInteger('lead_days')->nullable();
            $table->unsignedInteger('moq')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['supplier_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
