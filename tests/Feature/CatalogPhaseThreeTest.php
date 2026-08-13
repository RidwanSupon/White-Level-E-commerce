<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CatalogCsvService;
use App\Services\InventoryService;
use App\Services\ProductVariantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_variant_matrix_generator_creates_variants()
    {
        $product = Product::first();
        $service = new ProductVariantService();

        $variants = $service->generateVariantMatrix($product, [1, 2], 120000.00, 15);

        $this->assertCount(2, $variants);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'price' => 120000.00]);
    }

    public function test_live_search_autocomplete_api_returns_matches()
    {
        $product = Product::first();

        $response = $this->getJson('/api/v1/search/autocomplete?q=' . substr($product->name, 0, 4));

        $response->assertStatus(200);
        $response->assertJsonStructure(['results' => [['id', 'name', 'slug', 'price', 'url']]]);
    }

    public function test_inventory_service_reserves_and_commits_stock()
    {
        $product = Product::first();
        $initialStock = $product->stock_quantity;
        $service = new InventoryService();

        $reserved = $service->reserveStock($product, null, 2);
        $this->assertTrue($reserved);

        $service->commitStock($product, null, 2, 'TEST-ORDER-101');
        $this->assertEquals($initialStock - 2, $product->fresh()->stock_quantity);
    }

    public function test_catalog_csv_export_and_import()
    {
        $service = new CatalogCsvService();
        $csvContent = $service->exportCsv();

        $this->assertStringContainsString('sku', $csvContent);
        $this->assertStringContainsString('price', $csvContent);

        // Test import
        $newCsv = "id,name,sku,price,compare_price,stock_quantity,category_name,featured_image\n";
        $newCsv .= "\"\",\"CSV Laptop Pro\",\"SKU-CSV-999\",\"150000\",\"0\",\"25\",\"Laptops\",\"https://images.unsplash.com/photo-1523275335684-37898b6baf30\"\n";

        $count = $service->importCsv($newCsv);
        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('products', ['sku' => 'SKU-CSV-999']);
    }

    public function test_direct_multiple_product_images_upload_and_delete()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $category = Category::first();

        $file1 = \Illuminate\Http\UploadedFile::fake()->image('picture1.png');
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('picture2.png');

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Multi Image Camera',
            'sku' => 'CAM-MULTI-01',
            'price' => 75000,
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 3,
            'product_images' => [$file1, $file2],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'CAM-MULTI-01')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);

        // Test Setting Primary 1st Image Action
        $secondImgId = $product->images->last()->id;
        $primaryResp = $this->actingAs($admin)->post(route('admin.products.images.primary', $secondImgId));
        $primaryResp->assertRedirect();
        $this->assertEquals($product->images->last()->image_path, $product->fresh()->featured_image);

        // Test Image Deletion
        $imgId = $product->images->first()->id;
        $deleteResp = $this->actingAs($admin)->delete(route('admin.products.images.destroy', $imgId));
        $deleteResp->assertRedirect();
        $this->assertCount(1, $product->fresh()->images);
    }

    public function test_admin_can_toggle_product_published_status()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $product = Product::first();

        $initialStatus = $product->is_active;

        $response = $this->actingAs($admin)->patch(route('admin.products.toggle_status', $product->id));
        $response->assertRedirect();

        $this->assertEquals(!$initialStatus, $product->fresh()->is_active);
    }
}
