<?php

namespace App\Services;

use App\Models\ProductLevel;
use App\Models\Varient;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OrderPricingService
{
    public function normalize(Collection|array $submittedItems, int $sellerLevelId): Collection
    {
        $items = collect($submittedItems)->values();
        $variantIds = $items->pluck('product_variant_id')->filter()->map(fn ($id) => (int) $id)->unique();

        $variants = Varient::query()
            ->with('product:id,is_active,pricing_model')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $rules = ProductLevel::query()
            ->where('level_id', $sellerLevelId)
            ->whereIn('product_id', $items->pluck('product_id')->map(fn ($id) => (int) $id)->unique())
            ->get(['product_id', 'type', 'value'])
            ->keyBy('product_id');

        return $items->map(function (array $item, int $index) use ($variants, $rules) {
            $productId = (int) $item['product_id'];
            $variantId = (int) ($item['product_variant_id'] ?? 0);
            $quantity = max(1, (int) $item['quantity']);
            $variant = $variants->get($variantId);

            if (! $variant || (int) $variant->product_id !== $productId) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_variant_id" => 'The selected variant does not belong to this product.',
                ]);
            }

            if (! $variant->is_active || ! $variant->product?->is_active) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_variant_id" => 'This product variant is not available.',
                ]);
            }

            $pricingModel = $variant->product->pricing_model === 'reseller' ? 'reseller' : 'commission';

            if ($pricingModel === 'reseller') {
                if ($variant->reseller_price === null) {
                    throw ValidationException::withMessages([
                        "items.{$index}.price" => 'This reseller product does not have a reseller price.',
                    ]);
                }

                $resellerPrice = round((float) $variant->reseller_price, 2);
                $maximumPrice = $variant->maximum_selling_price === null
                    ? null
                    : round((float) $variant->maximum_selling_price, 2);
                $sellingPrice = round((float) $item['price'], 2);

                if ($sellingPrice < $resellerPrice || ($maximumPrice !== null && $sellingPrice > $maximumPrice)) {
                    $message = $maximumPrice === null
                        ? sprintf('Selling price must be at least LKR %s.', number_format($resellerPrice, 2))
                        : sprintf(
                            'Selling price must be between LKR %s and LKR %s.',
                            number_format($resellerPrice, 2),
                            number_format($maximumPrice, 2)
                        );

                    throw ValidationException::withMessages([
                        "items.{$index}.price" => $message,
                    ]);
                }

                $unitEarning = $sellingPrice - $resellerPrice;
            } else {
                $resellerPrice = null;
                $sellingPrice = round((float) $variant->price, 2);
                $rule = $rules->get($productId);
                $unitEarning = ! $rule
                    ? 0.0
                    : ($rule->type === 'percentage'
                        ? $sellingPrice * ((float) $rule->value / 100)
                        : (float) $rule->value);
            }

            return [
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $sellingPrice,
                'pricing_model' => $pricingModel,
                'reseller_price' => $resellerPrice,
                'seller_earning_amount' => round($unitEarning * $quantity, 2),
            ];
        });
    }
}
