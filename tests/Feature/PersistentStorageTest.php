<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersistentStorageTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->category = Category::create([
            'name' => 'Storage Category',
            'slug' => 'storage-category',
        ]);
    }

    /** @test */
    public function test_01_product_image_is_uploaded_using_storage_abstraction()
    {
        Storage::fake('supabase');
        config(['filesystems.default' => 'supabase']);

        $file = UploadedFile::fake()->image('product_test.jpg', 800, 800);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Storage Test Product',
            'sku' => 'STOR-001',
            'price' => 500,
            'category_id' => $this->category->id,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'product_images' => [$file],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'STOR-001')->firstOrFail();
        $this->assertNotEmpty($product->featured_image);

        // Verify file exists on Supabase Storage disk
        Storage::disk('supabase')->assertExists($product->featured_image);
    }

    /** @test */
    public function test_02_product_image_deletion_removes_file_from_storage()
    {
        Storage::fake('supabase');
        config(['filesystems.default' => 'supabase']);

        $file = UploadedFile::fake()->image('to_delete.jpg', 400, 400);
        $path = app(StorageService::class)->upload($file, 'products/99/images');

        $product = Product::create([
            'name' => 'Product For Delete Test',
            'slug' => 'product-delete-test',
            'sku' => 'DEL-001',
            'price' => 100,
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'featured_image' => $path,
        ]);

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path,
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        Storage::disk('supabase')->assertExists($path);

        // Delete gallery image via controller
        $this->actingAs($this->admin)->delete(route('admin.products.images.destroy', $image->id));

        // Verify file deleted from Supabase Storage
        Storage::disk('supabase')->assertMissing($path);
    }

    /** @test */
    public function test_03_branding_logo_upload_uses_storage_service()
    {
        Storage::fake('public');
        config(['filesystems.default' => 'public']);

        $file = UploadedFile::fake()->image('logo.png', 300, 100);

        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'site_name' => 'LuxeCart Test',
            'site_logo_file' => $file,
        ]);

        $response->assertSessionHas('success');

        $logoSetting = setting('site_logo');
        $this->assertNotEmpty($logoSetting);
        Storage::disk('public')->assertExists($logoSetting);
    }

    /** @test */
    public function test_04_storage_migrate_command_discovers_and_uploads_local_files()
    {
        Storage::fake('supabase');

        // Create temporary local file in public/uploads/test_migrate.png
        $testDir = public_path('uploads/test_migrate');
        if (!File::isDirectory($testDir)) {
            File::makeDirectory($testDir, 0755, true);
        }
        $testFile = $testDir . '/sample.png';
        File::put($testFile, 'fake_png_data');

        $this->artisan('storage:migrate-to-supabase', ['--disk' => 'supabase'])
            ->assertExitCode(0);

        Storage::disk('supabase')->assertExists('uploads/test_migrate/sample.png');

        // Clean up temporary local test file
        File::delete($testFile);
        @rmdir($testDir);
    }
}
