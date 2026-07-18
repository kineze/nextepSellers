<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_products_receive_a_generated_rating_and_user_count(): void
    {
        $category = Category::create([
            'name' => 'Rating Test',
            'slug' => 'rating-test',
            'is_active' => true,
        ]);

        $product = Product::create([
            'title' => 'Generated Rating Product',
            'small_description' => 'A product used to verify generated ratings.',
            'category_id' => $category->id,
            'product_code' => 'RATING-TEST-1',
            'is_active' => true,
            'has_varients' => false,
        ]);

        $this->assertGreaterThanOrEqual(4.0, (float) $product->rating);
        $this->assertLessThanOrEqual(5.0, (float) $product->rating);
        $this->assertGreaterThanOrEqual(1, $product->rating_user_count);
        $this->assertLessThan(1000, $product->rating_user_count);
    }
}
