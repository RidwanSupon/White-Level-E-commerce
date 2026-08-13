<?php

namespace Tests\Feature;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\User;
use App\Services\ShippingCalculatorService;
use App\Services\SmsOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentShippingPhaseSixTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_admin_can_create_shipping_zone_and_method()
    {
        $zoneResponse = $this->actingAs($this->admin)->post(route('admin.shipping.zones.store'), [
            'name' => 'Chittagong Metro',
            'regions_json' => 'Chittagong City, Agrabad',
        ]);

        $zoneResponse->assertRedirect();
        $this->assertDatabaseHas('shipping_zones', ['name' => 'Chittagong Metro']);

        $zone = ShippingZone::where('name', 'Chittagong Metro')->first();

        $methodResponse = $this->actingAs($this->admin)->post(route('admin.shipping.methods.store'), [
            'shipping_zone_id' => $zone->id,
            'name' => 'Express Courier',
            'cost' => 120.00,
            'free_shipping_threshold' => 3000.00,
            'estimated_days' => '1-2 Days',
        ]);

        $methodResponse->assertRedirect();
        $this->assertDatabaseHas('shipping_methods', ['name' => 'Express Courier']);
    }

    public function test_admin_can_update_payment_gateway_settings()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payment_methods.update'), [
            'gateway_stripe_enabled' => '1',
            'gateway_stripe_mode' => 'sandbox',
            'gateway_stripe_secret_key' => 'sk_test_custom_key_123',
        ]);

        $response->assertRedirect();
        $this->assertEquals('sk_test_custom_key_123', setting('gateway_stripe_secret_key'));
    }

    public function test_shipping_calculator_service_calculates_rates()
    {
        $method = ShippingMethod::first();
        $service = new ShippingCalculatorService();

        $feeBelowThreshold = $service->calculateFee($method->id, 500.00);
        $this->assertEquals((float) $method->charge, $feeBelowThreshold);

        if ($method->free_shipping_threshold) {
            $feeAboveThreshold = $service->calculateFee($method->id, $method->free_shipping_threshold + 500);
            $this->assertEquals(0.00, $feeAboveThreshold);
        }
    }

    public function test_sms_otp_service_generates_and_verifies_otp()
    {
        $service = new SmsOtpService();
        $phone = '01700000000';

        $otp = $service->generateOtp($phone);
        $this->assertEquals(6, strlen($otp));

        $isValid = $service->verifyOtp($phone, $otp);
        $this->assertTrue($isValid);

        $isInvalid = $service->verifyOtp($phone, '000000');
        $this->assertFalse($isInvalid);
    }
}
