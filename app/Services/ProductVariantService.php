<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductVariantService
{
    public function generateVariantMatrix(Product $product, array $attributeValueIds, float $defaultPrice = null, int $defaultStock = 0): array
    {
        $createdVariants = [];
        $defaultPrice = $defaultPrice ?? $product->price;

        foreach ($attributeValueIds as $valueId) {
            $sku = $product->sku . '-VAR-' . $valueId;
            
            $variant = ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'sku' => $sku],
                [
                    'price' => $defaultPrice,
                    'stock_quantity' => $defaultStock,
                    'is_active' => true,
                ]
            );

            $variant->attributeValues()->syncWithoutDetaching([$valueId]);
            $createdVariants[] = $variant;
        }

        return $createdVariants;
    }
}
