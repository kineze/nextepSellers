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
        if (!Schema::hasColumn('sellers', 'first_payment_date')) {
            return;
        }

        if (Schema::hasColumn('sellers', 'first_success_order_date')) {
            return;
        }

        Schema::table('sellers', function (Blueprint $table) {
            $table->renameColumn('first_payment_date', 'first_success_order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('sellers', 'first_success_order_date')) {
            return;
        }

        if (Schema::hasColumn('sellers', 'first_payment_date')) {
            return;
        }

        Schema::table('sellers', function (Blueprint $table) {
            $table->renameColumn('first_success_order_date', 'first_payment_date');
        });
    }
};
