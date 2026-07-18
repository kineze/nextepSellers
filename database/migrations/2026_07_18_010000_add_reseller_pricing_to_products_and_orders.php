<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('pricing_model', 20)->default('commission')->after('product_code')->index();
        });

        Schema::table('varients', function (Blueprint $table) {
            $table->decimal('reseller_price', 12, 2)->nullable()->after('price');
            $table->decimal('maximum_selling_price', 12, 2)->nullable()->after('reseller_price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('pricing_model', 20)->default('commission')->after('price');
            $table->decimal('reseller_price', 12, 2)->nullable()->after('pricing_model');
            $table->decimal('seller_earning_amount', 12, 2)->default(0)->after('reseller_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['pricing_model', 'reseller_price', 'seller_earning_amount']);
        });

        Schema::table('varients', function (Blueprint $table) {
            $table->dropColumn(['reseller_price', 'maximum_selling_price']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['pricing_model']);
            $table->dropColumn('pricing_model');
        });
    }
};
