<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_levels', function (Blueprint $table) {
            $table->enum('affiliate_commission_type', ['percentage', 'amount'])
                ->default('percentage')
                ->after('value');
            $table->decimal('affiliate_commission', 12, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_levels', function (Blueprint $table) {
            $table->dropColumn('affiliate_commission_type');
            $table->decimal('affiliate_commission', 5, 2)->default(0)->change();
        });
    }
};
