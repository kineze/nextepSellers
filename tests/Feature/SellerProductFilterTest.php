<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_filtered_by_attributes_collection_and_date_order(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::create([
            'name' => 'Filter Test',
            'slug' => 'filter-test',
            'is_active' => true,
        ]);
        Attribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'string',
            'values' => ['Small', 'Large'],
        ]);
        Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'color',
            'values' => [
                ['name' => 'Red', 'color' => '#ef4444'],
                ['name' => 'Blue', 'color' => '#3b82f6'],
            ],
        ]);

        $oldSmall = $this->createProduct($category, 'OLD-SMALL', ['size' => 'Small'], false, now()->subDays(60));
        $newLarge = $this->createProduct($category, 'NEW-LARGE', [
            'size' => 'Large',
            'color' => ['name' => 'Red', 'color' => '#ef4444'],
        ], true, now()->subDays(2));
        $newSmall = $this->createProduct($category, 'NEW-SMALL', ['size' => 'Small'], false, now()->subDay());

        $attributeResponse = $this->getJson(route('sellerProducts', [
            'sort' => 'oldest',
            'attributes' => ['size' => 'Small'],
        ]));

        $attributeResponse->assertOk();
        $attributeResponse->assertJsonPath('products.0.id', $oldSmall->id);
        $attributeResponse->assertJsonPath('products.1.id', $newSmall->id);
        $attributeResponse->assertJsonCount(2, 'products');

        $colorResponse = $this->getJson(route('sellerProducts', [
            'attributes' => ['color' => 'Red'],
        ]));

        $colorResponse->assertOk();
        $colorResponse->assertJsonCount(1, 'products');
        $colorResponse->assertJsonPath('products.0.id', $newLarge->id);

        $bestSellingResponse = $this->getJson(route('sellerProducts', [
            'collection' => 'best_selling',
        ]));

        $bestSellingResponse->assertOk();
        $bestSellingResponse->assertJsonCount(1, 'products');
        $bestSellingResponse->assertJsonPath('products.0.id', $newLarge->id);

        $newArrivalResponse = $this->getJson(route('sellerProducts', [
            'collection' => 'new_arrivals',
        ]));

        $newArrivalResponse->assertOk();
        $newArrivalResponse->assertJsonCount(2, 'products');

        $newLarge->update(['pricing_model' => 'reseller']);
        $newLarge->varients()->update([
            'reseller_price' => 800,
            'maximum_selling_price' => 1200,
        ]);

        $marginResponse = $this->getJson(route('sellerProducts', ['pricing_model' => 'reseller']));
        $marginResponse->assertOk();
        $marginResponse->assertJsonCount(1, 'products');
        $marginResponse->assertJsonPath('products.0.id', $newLarge->id);
        $marginResponse->assertJsonPath('products.0.pricing_model', 'reseller');
        $marginResponse->assertJsonPath('products.0.max_price', 1200);

        $newLarge->varients()->update(['maximum_selling_price' => null]);
        $unlimitedMarginResponse = $this->getJson(route('sellerProducts', ['pricing_model' => 'reseller']));
        $unlimitedMarginResponse->assertOk();
        $unlimitedMarginResponse->assertJsonPath('products.0.max_price', null);
        $unlimitedMarginResponse->assertJsonPath('products.0.commission', null);

        $commissionResponse = $this->getJson(route('sellerProducts', ['pricing_model' => 'commission']));
        $commissionResponse->assertOk();
        $commissionResponse->assertJsonCount(2, 'products');
    }

    private function createProduct(Category $category, string $code, array $attributes, bool $bestSeller, $createdAt): Product
    {
        $product = Product::create([
            'title' => $code,
            'small_description' => 'Filter test product.',
            'category_id' => $category->id,
            'product_code' => $code,
            'is_active' => true,
            'isbestseller' => $bestSeller,
            'has_varients' => true,
        ]);

        $product->varients()->create([
            'sku' => $code.'-SKU',
            'attributes' => $attributes,
            'price' => 1000,
            'stock_quantity' => 10,
            'reorder_level' => 2,
            'is_active' => true,
        ]);
        $product->forceFill(['created_at' => $createdAt])->saveQuietly();

        return $product;
    }
}
