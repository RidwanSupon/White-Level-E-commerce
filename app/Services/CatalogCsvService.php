<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class CatalogCsvService
{
    public function exportCsv(): string
    {
        $products = Product::with('category')->get();
        $output = "id,name,sku,price,compare_price,stock_quantity,category_name,featured_image\n";

        foreach ($products as $prod) {
            $catName = str_replace('"', '""', $prod->category?->name ?? 'General');
            $name = str_replace('"', '""', $prod->name);
            $output .= "\"{$prod->id}\",\"{$name}\",\"{$prod->sku}\",\"{$prod->price}\",\"{$prod->compare_price}\",\"{$prod->stock_quantity}\",\"{$catName}\",\"{$prod->featured_image}\"\n";
        }

        return $output;
    }

    public function importCsv(string $csvContent): int
    {
        $lines = explode("\n", trim($csvContent));
        if (count($lines) < 2) return 0;

        $importedCount = 0;
        $header = str_getcsv(array_shift($lines));

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $row = str_getcsv($line);
            if (count($row) < 4) continue;

            $name = $row[1] ?? 'Imported Product';
            $sku = $row[2] ?? ('SKU-' . rand(1000, 9999));
            $price = (float) ($row[3] ?? 100);
            $stock = (int) ($row[5] ?? 10);
            $catName = $row[6] ?? 'General';
            $img = $row[7] ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80';

            $category = Category::firstOrCreate(
                ['name' => $catName],
                ['slug' => Str::slug($catName), 'is_active' => true]
            );

            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . Str::random(4),
                    'price' => $price,
                    'stock_quantity' => $stock,
                    'category_id' => $category->id,
                    'featured_image' => $img,
                    'is_active' => true,
                ]
            );

            $importedCount++;
        }

        return $importedCount;
    }
}
