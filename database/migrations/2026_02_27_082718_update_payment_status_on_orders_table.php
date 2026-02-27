<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')
            ->whereNull('payment_status')
            ->orWhere('payment_status', '')
            ->update(['payment_status' => 'pending']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `payment_status` ENUM('pending','available','paid') NOT NULL DEFAULT 'pending'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_status SET DEFAULT 'pending'");
            DB::statement("UPDATE orders SET payment_status = 'pending' WHERE payment_status IS NULL OR payment_status = ''");
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_status SET NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `payment_status` VARCHAR(30) NULL DEFAULT NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_status DROP NOT NULL");
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_status DROP DEFAULT");
        }
    }
};
