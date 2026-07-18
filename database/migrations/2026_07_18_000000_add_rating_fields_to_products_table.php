<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('rating', 2, 1)->default(4.0)->after('isbestseller');
            $table->unsignedInteger('rating_user_count')->default(0)->after('rating');
        });

        DB::table('products')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($products) {
                foreach ($products as $product) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'rating' => random_int(40, 50) / 10,
                            'rating_user_count' => random_int(1, 999),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_user_count']);
        });
    }
};
