<?php

namespace Tests\Unit;

use App\Http\Controllers\OrderReturnController;
use App\Models\LotItem;
use App\Models\OrderReturn;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderReturnControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->foreignId('seller_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('waybill_no')->nullable();
            $table->string('delivery_status')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
        });
        Schema::create('varients', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->json('attributes')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->timestamps();
        });
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id');
            $table->string('lot_number');
            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
        Schema::create('lot_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id');
            $table->foreignId('variant_id');
            $table->foreignId('order_id')->nullable();
            $table->string('barcode')->unique();
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string('status');
            $table->dateTime('finalized_at')->nullable();
            $table->timestamps();
        });
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id');
            $table->foreignId('lot_item_id');
            $table->foreignId('lot_id');
            $table->foreignId('variant_id');
            $table->string('barcode');
            $table->string('disposition');
            $table->timestamps();
        });

        DB::table('orders')->insert([
            'id' => 1,
            'status' => 'cancelled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('varients')->insert([
            'id' => 1,
            'stock_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('lots')->insert([
            'id' => 1,
            'variant_id' => 1,
            'lot_number' => '10',
            'quantity' => 0,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('lot_items')->insert([
            [
                'id' => 1,
                'lot_id' => 1,
                'variant_id' => 1,
                'order_id' => 1,
                'barcode' => '10-000001',
                'status' => 'sold',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'lot_id' => 1,
                'variant_id' => 1,
                'order_id' => 1,
                'barcode' => '10-000002',
                'status' => 'sold',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('order_returns')->insert([
            'id' => 1,
            'order_id' => 1,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('order_returns');
        Schema::dropIfExists('lot_items');
        Schema::dropIfExists('lots');
        Schema::dropIfExists('varients');
        Schema::dropIfExists('orders');

        parent::tearDown();
    }

    #[Test]
    public function it_restock_accepts_only_after_every_barcode_is_scanned_and_finalized(): void
    {
        $controller = new OrderReturnController();
        $orderReturn = OrderReturn::query()->findOrFail(1);

        $controller->scanLotItem($this->scanRequest('10-000001', 'returned'), $orderReturn);

        $this->assertSame('returned', LotItem::query()->findOrFail(1)->status);
        $this->assertSame(0, (int) DB::table('lots')->where('id', 1)->value('quantity'));
        $this->assertSame(0, (int) DB::table('varients')->where('id', 1)->value('stock_quantity'));

        try {
            $controller->finalize($orderReturn);
            $this->fail('Finalization should require every allocated barcode.');
        } catch (ValidationException $e) {
            $this->assertSame(
                'Scan every allocated barcode before finalizing this return.',
                $e->errors()['return'][0]
            );
        }

        $controller->scanLotItem($this->scanRequest('10-000002', 'damaged'), $orderReturn);
        $controller->finalize($orderReturn);

        $acceptedItem = LotItem::query()->findOrFail(1);
        $damagedItem = LotItem::query()->findOrFail(2);

        $this->assertSame('available', $acceptedItem->status);
        $this->assertNull($acceptedItem->order_id);
        $this->assertSame('damaged', $damagedItem->status);
        $this->assertNull($damagedItem->order_id);
        $this->assertSame(1, (int) DB::table('lots')->where('id', 1)->value('quantity'));
        $this->assertSame(1, (int) DB::table('varients')->where('id', 1)->value('stock_quantity'));
        $this->assertSame('completed', DB::table('order_returns')->where('id', 1)->value('status'));
    }

    private function scanRequest(string $barcode, string $disposition): Request
    {
        return Request::create('/api/admin/returns/1/scan-lot-item', 'POST', [
            'barcode' => $barcode,
            'disposition' => $disposition,
        ]);
    }
}
