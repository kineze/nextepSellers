<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Level;
use App\Models\Product;
use App\Services\OrderPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_price_is_authoritative_and_reseller_earning_is_margin_only(): void
    {
        [$level, $commissionProduct, $commissionVariant] = $this->createProduct('commission');
        $commissionProduct->productLevels()->create([
            'level_id' => $level->id,
            'type' => 'percentage',
            'value' => 10,
            'affiliate_commission_type' => 'percentage',
            'affiliate_commission' => 5,
        ]);
        [, $resellerProduct, $resellerVariant] = $this->createProduct('reseller', $level);

        $items = app(OrderPricingService::class)->normalize([
            [
                'product_id' => $commissionProduct->id,
                'product_variant_id' => $commissionVariant->id,
                'quantity' => 2,
                'price' => 1,
            ],
            [
                'product_id' => $resellerProduct->id,
                'product_variant_id' => $resellerVariant->id,
                'quantity' => 2,
                'price' => 1300,
            ],
        ], $level->id);

        $this->assertSame(1000.0, $items[0]['price']);
        $this->assertSame(200.0, $items[0]['seller_earning_amount']);
        $this->assertSame(1300.0, $items[1]['price']);
        $this->assertSame(600.0, $items[1]['seller_earning_amount']);
    }

    public function test_reseller_price_accepts_boundaries_and_rejects_prices_outside_them(): void
    {
        [$level, $product, $variant] = $this->createProduct('reseller');
        $service = app(OrderPricingService::class);

        foreach ([1000, 1500] as $price) {
            $result = $service->normalize([[
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
                'price' => $price,
            ]], $level->id);
            $this->assertSame((float) $price, $result[0]['price']);
        }

        foreach ([999.99, 1500.01] as $price) {
            try {
                $service->normalize([[
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                    'price' => $price,
                ]], $level->id);
                $this->fail('Expected out-of-range reseller price to be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('items.0.price', $exception->errors());
            }
        }
    }

    public function test_reseller_price_without_a_maximum_has_no_upper_limit(): void
    {
        [$level, $product, $variant] = $this->createProduct('reseller');
        $variant->update(['maximum_selling_price' => null]);
        $service = app(OrderPricingService::class);

        $result = $service->normalize([[
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 5000,
        ]], $level->id);

        $this->assertSame(5000.0, $result[0]['price']);
        $this->assertSame(8000.0, $result[0]['seller_earning_amount']);

        try {
            $service->normalize([[
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
                'price' => 999.99,
            ]], $level->id);
            $this->fail('Expected a price below the reseller price to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Selling price must be at least LKR 1,000.00.',
                $exception->errors()['items.0.price'][0]
            );
        }
    }

    private function createProduct(string $pricingModel, ?Level $level = null): array
    {
        $level ??= Level::create([
            'level_no' => Level::query()->max('level_no') + 1,
            'points' => 0,
            'description' => 'Pricing test level',
        ]);
        $category = Category::firstOrCreate(
            ['slug' => 'pricing-tests'],
            ['name' => 'Pricing Tests', 'is_active' => true]
        );
        $code = strtoupper($pricingModel).'-'.uniqid();
        $product = Product::create([
            'title' => $code,
            'small_description' => 'Pricing test product.',
            'category_id' => $category->id,
            'product_code' => $code,
            'pricing_model' => $pricingModel,
            'is_active' => true,
            'has_varients' => false,
        ]);
        $variant = $product->varients()->create([
            'sku' => $code.'-SKU',
            'attributes' => [],
            'price' => 1000,
            'reseller_price' => $pricingModel === 'reseller' ? 1000 : null,
            'maximum_selling_price' => $pricingModel === 'reseller' ? 1500 : null,
            'stock_quantity' => 10,
            'reorder_level' => 1,
            'is_active' => true,
        ]);

        return [$level, $product, $variant];
    }
}
