<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductImageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->category = Category::first();
    }

    /** @test */
    public function test_1_delete_primary_image_promotes_next_image_to_primary()
    {
        $product = Product::create([
            'name' => 'Test Phone',
            'slug' => 'test-phone',
            'sku' => 'TP-001',
            'price' => 1000,
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'featured_image' => '/uploads/products/img1.png',
        ]);

        $img1 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/img1.png', 'sort_order' => 1, 'is_primary' => true]);
        $img2 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/img2.png', 'sort_order' => 2, 'is_primary' => false]);
        $img3 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/img3.png', 'sort_order' => 3, 'is_primary' => false]);

        // Act: Delete Primary Image 1
        $response = $this->actingAs($this->admin)->delete(route('admin.products.images.destroy', $img1->id));
        $response->assertRedirect();

        // Assert: Image 1 is completely deleted
        $this->assertDatabaseMissing('product_images', ['id' => $img1->id]);

        // Assert: Image 2 is automatically promoted to Primary and featured_image updated
        $freshProduct = $product->fresh();
        $this->assertEquals('/uploads/products/img2.png', $freshProduct->featured_image);
        $this->assertTrue((bool)$img2->fresh()->is_primary);
    }

    /** @test */
    public function test_2_delete_non_primary_image_keeps_original_primary()
    {
        $product = Product::create([
            'name' => 'Test Laptop',
            'slug' => 'test-laptop',
            'sku' => 'TL-002',
            'price' => 2000,
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'featured_image' => '/uploads/products/img1.png',
        ]);

        $img1 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/img1.png', 'sort_order' => 1, 'is_primary' => true]);
        $img2 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/img2.png', 'sort_order' => 2, 'is_primary' => false]);

        // Act: Delete Image 2 (Non-primary)
        $response = $this->actingAs($this->admin)->delete(route('admin.products.images.destroy', $img2->id));
        $response->assertRedirect();

        // Assert: Image 2 deleted, Image 1 remains Primary
        $this->assertDatabaseMissing('product_images', ['id' => $img2->id]);
        $this->assertEquals('/uploads/products/img1.png', $product->fresh()->featured_image);
        $this->assertTrue((bool)$img1->fresh()->is_primary);
    }

    /** @test */
    public function test_3_delete_only_remaining_image_resets_featured_image_to_fallback()
    {
        $product = Product::create([
            'name' => 'Single Image Item',
            'slug' => 'single-image-item',
            'sku' => 'SII-003',
            'price' => 500,
            'category_id' => $this->category->id,
            'stock_quantity' => 1,
            'low_stock_threshold' => 1,
            'featured_image' => '/uploads/products/sole.png',
        ]);

        $img = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/sole.png', 'sort_order' => 1, 'is_primary' => true]);

        // Act: Delete sole image
        $response = $this->actingAs($this->admin)->delete(route('admin.products.images.destroy', $img->id));
        $response->assertRedirect();

        // Assert: Image deleted, product has no image records, featured_image_url returns placeholder
        $this->assertCount(0, $product->fresh()->images);
        $this->assertStringContainsString('placeholder.png', $product->fresh()->featured_image_url);
    }

    /** @test */
    public function test_4_upload_new_gallery_images()
    {
        $file1 = UploadedFile::fake()->image('pic1.jpg');
        $file2 = UploadedFile::fake()->image('pic2.png');

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'New Multi Image Product',
            'sku' => 'NMIP-100',
            'price' => 1500,
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'product_images' => [$file1, $file2],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'NMIP-100')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);
    }

    /** @test */
    public function test_5_set_image_3_as_primary()
    {
        $product = Product::create([
            'name' => 'Camera Set',
            'slug' => 'camera-set',
            'sku' => 'CAM-333',
            'price' => 45000,
            'category_id' => $this->category->id,
            'stock_quantity' => 4,
            'low_stock_threshold' => 1,
            'featured_image' => '/uploads/products/c1.png',
        ]);

        $c1 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/c1.png', 'sort_order' => 1, 'is_primary' => true]);
        $c2 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/c2.png', 'sort_order' => 2, 'is_primary' => false]);
        $c3 = ProductImage::create(['product_id' => $product->id, 'image_path' => '/uploads/products/c3.png', 'sort_order' => 3, 'is_primary' => false]);

        // Act: Set c3 as primary
        $response = $this->actingAs($this->admin)->post(route('admin.products.images.primary', $c3->id));
        $response->assertRedirect();

        // Assert: c3 is primary and product featured_image is updated to c3
        $this->assertEquals('/uploads/products/c3.png', $product->fresh()->featured_image);
        $this->assertTrue((bool)$c3->fresh()->is_primary);
    }

    /** @test */
    public function test_6_physical_file_is_removed_on_image_delete()
    {
        $filename = 'to_be_deleted_' . time() . '.png';
        $relative = '/uploads/products/' . $filename;
        $fullPath = public_path('uploads/products/' . $filename);

        if (!File::exists(public_path('uploads/products'))) {
            File::makeDirectory(public_path('uploads/products'), 0755, true);
        }

        File::put($fullPath, 'fake-image-content');
        $this->assertTrue(File::exists($fullPath));

        $product = Product::create([
            'name' => 'File Cleanup Test Item',
            'slug' => 'file-cleanup-test-item',
            'sku' => 'FCT-001',
            'price' => 100,
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'featured_image' => $relative,
        ]);

        $img = ProductImage::create(['product_id' => $product->id, 'image_path' => $relative, 'sort_order' => 1, 'is_primary' => true]);

        // Act: Delete image
        $response = $this->actingAs($this->admin)->delete(route('admin.products.images.destroy', $img->id));
        $response->assertRedirect();

        // Assert: Database record and physical file are both removed
        $this->assertDatabaseMissing('product_images', ['id' => $img->id]);
        $this->assertFalse(File::exists($fullPath));
    }
}
