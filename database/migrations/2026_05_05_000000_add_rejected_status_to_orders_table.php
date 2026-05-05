<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('draft','approved','confirmed','packed','shipped','completed','cancelled','rejected') NOT NULL DEFAULT 'draft'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE orders ALTER COLUMN status SET DEFAULT 'draft'");
            DB::statement("ALTER TABLE orders ALTER COLUMN status SET NOT NULL");
        }
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('status', 'rejected')
            ->update([
                'status' => 'draft',
                'is_draft' => true,
            ]);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('draft','approved','confirmed','packed','shipped','completed','cancelled') NOT NULL DEFAULT 'draft'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE orders ALTER COLUMN status SET DEFAULT 'draft'");
            DB::statement("ALTER TABLE orders ALTER COLUMN status SET NOT NULL");
        }
    }
};
