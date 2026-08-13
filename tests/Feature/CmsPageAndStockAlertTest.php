<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\Services\StockAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CmsPageAndStockAlertTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->customer = User::where('email', 'customer@example.com')->first();

        $category = Category::first();
        $this->product = Product::create([
            'name' => 'Stock Alert Headphones',
            'slug' => 'stock-alert-headphones',
            'sku' => 'SAH-001',
            'price' => 1500,
            'category_id' => $category->id,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_1_admin_creates_published_cms_page_customer_can_access()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
            'title' => 'About Our Store',
            'content' => 'Welcome to our e-commerce store!',
            'meta_title' => 'About Us - LuxeCart',
            'meta_description' => 'Learn about our store background.',
            'is_published' => '1',
            'show_in_footer' => '1',
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $page = Page::where('slug', 'about-our-store')->first();
        $this->assertNotNull($page);
        $this->assertTrue($page->is_published);
        $this->assertTrue($page->show_in_footer);

        // Customer accesses published page
        $custResponse = $this->get(route('page.show', 'about-our-store'));
        $custResponse->assertStatus(200);
        $custResponse->assertSee('About Our Store');
        $custResponse->assertSee('Welcome to our e-commerce store!');
    }

    /** @test */
    public function test_2_admin_creates_draft_cms_page_customer_gets_404()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
            'title' => 'Secret Draft Policy',
            'content' => 'Internal unreleased terms.',
            'meta_title' => 'Draft Policy',
        ]);

        $page = Page::where('slug', 'secret-draft-policy')->first();
        $this->assertNotNull($page);
        $this->assertFalse($page->is_published);

        // Customer attempts to access draft page directly
        $custResponse = $this->get(route('page.show', 'secret-draft-policy'));
        $custResponse->assertStatus(404);
    }

    /** @test */
    public function test_4_customer_opens_invalid_cms_slug_gets_404()
    {
        $response = $this->get('/pages/non-existent-page-slug');
        $response->assertStatus(404);
    }

    /** @test */
    public function test_5_published_cms_page_configured_for_footer_appears_in_footer()
    {
        Page::create([
            'title' => 'Returns Policy',
            'slug' => 'returns-policy',
            'content' => 'Return rules context',
            'is_published' => true,
            'show_in_footer' => true,
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Returns Policy');
    }

    /** @test */
    public function test_6_and_7_stock_at_threshold_triggers_low_stock_notification()
    {
        $service = app(StockAlertService::class);

        // Stock = 20, Threshold = 5 -> Normal
        $service->checkStockAndNotify($this->product);
        $initialNotifCount = DB::table('notifications')->count();

        // Decrement stock to 5 (Threshold)
        $this->product->update(['stock_quantity' => 5]);
        $service->checkStockAndNotify($this->product->fresh());

        $newNotifCount = DB::table('notifications')->count();
        $this->assertEquals($initialNotifCount + 1, $newNotifCount);

        $notif = DB::table('notifications')->latest('id')->first();
        $data = json_decode($notif->data, true);
        $this->assertEquals('low_stock', $data['alert_type']);
        $this->assertEquals($this->product->id, $data['product_id']);
    }

    /** @test */
    public function test_8_further_stock_decrease_does_not_spam_duplicate_notifications()
    {
        $service = app(StockAlertService::class);

        // Stock = 5 -> First Low Stock Alert
        $this->product->update(['stock_quantity' => 5]);
        $service->checkStockAndNotify($this->product->fresh());
        $count1 = DB::table('notifications')->count();

        // Stock = 4 -> Still Low Stock (No duplicate spam)
        $this->product->update(['stock_quantity' => 4]);
        $service->checkStockAndNotify($this->product->fresh());
        $count2 = DB::table('notifications')->count();

        $this->assertEquals($count1, $count2);
    }

    /** @test */
    public function test_9_stock_reaching_zero_triggers_out_of_stock_notification()
    {
        $service = app(StockAlertService::class);

        // Stock = 0 -> Out of Stock Alert
        $this->product->update(['stock_quantity' => 0]);
        $service->checkStockAndNotify($this->product->fresh());

        $notif = DB::table('notifications')->latest('id')->first();
        $data = json_decode($notif->data, true);
        $this->assertEquals('out_of_stock', $data['alert_type']);
        $this->assertEquals(0, $data['stock_quantity']);
    }

    /** @test */
    public function test_10_restocking_resets_alert_state_allowing_future_alert_trigger()
    {
        $service = app(StockAlertService::class);

        // 1. Stock = 5 -> Low Stock Alert #1
        $this->product->update(['stock_quantity' => 5]);
        $service->checkStockAndNotify($this->product->fresh());
        $firstAlertCount = DB::table('notifications')->count();

        // 2. Restock to 50 -> Alert state resets
        $this->product->update(['stock_quantity' => 50]);
        $service->checkStockAndNotify($this->product->fresh());

        // 3. Stock drops to 5 again -> Low Stock Alert #2 triggered
        $this->product->update(['stock_quantity' => 5]);
        $service->checkStockAndNotify($this->product->fresh());
        $secondAlertCount = DB::table('notifications')->count();

        $this->assertGreaterThan($firstAlertCount, $secondAlertCount);
    }

    /** @test */
    public function test_11_opening_notification_center_does_not_generate_duplicate_notifications()
    {
        $this->actingAs($this->admin)->get(route('admin.notifications.index'));
        $count1 = DB::table('notifications')->count();

        $this->actingAs($this->admin)->get(route('admin.notifications.index'));
        $count2 = DB::table('notifications')->count();

        $this->assertEquals($count1, $count2);
    }
}
