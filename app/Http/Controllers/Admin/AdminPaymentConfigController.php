<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminPaymentConfigController extends Controller
{
    public function index()
    {
        $gateways = [
            'cod' => [
                'name' => 'Cash on Delivery (COD)',
                'enabled' => setting('gateway_cod_enabled', '1') === '1',
                'instructions' => setting('gateway_cod_instructions', 'Pay cash upon receiving products from delivery agent.'),
            ],
            'stripe' => [
                'name' => 'Stripe Credit/Debit Card',
                'enabled' => setting('gateway_stripe_enabled', '1') === '1',
                'mode' => setting('gateway_stripe_mode', 'sandbox'),
                'public_key' => setting('gateway_stripe_public_key', 'pk_test_sample'),
                'secret_key' => setting('gateway_stripe_secret_key', 'sk_test_sample'),
            ],
            'sslcommerz' => [
                'name' => 'SSLCommerz Payment Gateway',
                'enabled' => setting('gateway_sslcommerz_enabled', '1') === '1',
                'mode' => setting('gateway_sslcommerz_mode', 'sandbox'),
                'store_id' => setting('gateway_sslcommerz_store_id', 'test_store'),
            ],
            'bkash' => [
                'name' => 'bKash Mobile Banking',
                'enabled' => setting('gateway_bkash_enabled', '1') === '1',
                'mode' => setting('gateway_bkash_mode', 'sandbox'),
                'app_key' => setting('gateway_bkash_app_key', 'test_key'),
            ],
            'nagad' => [
                'name' => 'Nagad Mobile Banking',
                'enabled' => setting('gateway_nagad_enabled', '1') === '1',
                'mode' => setting('gateway_nagad_mode', 'sandbox'),
                'merchant_id' => setting('gateway_nagad_merchant_id', 'test_merchant'),
            ],
        ];

        return view('admin.payment_methods.index', compact('gateways'));
    }

    public function update(Request $request)
    {
        $settings = $request->except('_token');

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'payment_gateways.updated',
            'module' => 'payments',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Payment gateway settings updated successfully!');
    }
}
