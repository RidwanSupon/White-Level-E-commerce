<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsAuditPhaseEightTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_admin_can_view_analytics_reports_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Total Paid Revenue');
        $response->assertSee('Total Orders Placed');
    }

    public function test_admin_can_export_sales_report_csv()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertSee('order_number,customer,total');
    }

    public function test_admin_can_view_notification_center()
    {
        Product::first()->update(['stock_quantity' => 1, 'low_stock_threshold' => 5]);

        $response = $this->actingAs($this->admin)->get(route('admin.notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Notification Center');
        $response->assertSee('Low Stock Warnings');
    }

    public function test_audit_logs_record_and_filter()
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'security.password_changed',
            'module' => 'security',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit_logs.index', ['module' => 'security']));

        $response->assertStatus(200);
        $response->assertSee('security.password_changed');
    }
}
