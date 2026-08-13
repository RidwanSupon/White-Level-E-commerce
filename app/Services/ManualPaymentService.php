<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ManualPayment;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ManualPaymentService
{
    public function submitPayment(Order $order, array $data, string $paymentType = 'full_order'): array
    {
        $method = strtolower(trim($data['payment_method'])); // bkash, nagad
        $transactionId = strtoupper(trim($data['transaction_id']));

        if ($order->delivery_advance_required) {
            $paymentType = 'delivery_advance';
        }

        // 1. Prevent duplicate transaction ID submission across the platform
        $existing = ManualPayment::where('payment_method', $method)
            ->where('transaction_id', $transactionId)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'This Transaction ID has already been submitted for another order.'
            ];
        }

        // 2. Validate exact expected payment amount
        $expectedAmount = ($paymentType === 'delivery_advance')
            ? (float) $order->delivery_advance_amount
            : (float) $order->grand_total;

        if (isset($data['submitted_amount']) && (float) $data['submitted_amount'] != $expectedAmount) {
            return [
                'success' => false,
                'message' => "Invalid payment amount. You must pay EXACTLY " . format_price($expectedAmount) . " for this " . ($paymentType === 'delivery_advance' ? 'advance delivery fee.' : 'order.')
            ];
        }

        $merchantNumber = setting("{$method}_number", '01700000000');

        // 3. Handle optional / required payment proof screenshot file upload
        $proofPath = null;
        if (isset($data['payment_proof']) && $data['payment_proof'] instanceof UploadedFile) {
            $file = $data['payment_proof'];
            if ($file->isValid()) {
                $dir = public_path('uploads/payment_proofs');
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                $filename = 'proof_' . $order->id . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $file->move($dir, $filename);
                $proofPath = '/uploads/payment_proofs/' . $filename;
            }
        }

        return DB::transaction(function () use ($order, $method, $paymentType, $merchantNumber, $expectedAmount, $transactionId, $proofPath, $data) {
            $payment = ManualPayment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'payment_type' => $paymentType,
                'payment_method' => $method,
                'merchant_number' => $merchantNumber,
                'amount' => $expectedAmount,
                'transaction_id' => $transactionId,
                'payment_proof' => $proofPath,
                'status' => 'verification_pending',
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            $order->update([
                'status' => 'payment_verification_pending',
                'payment_status' => 'verification_pending',
                'payment_method' => $method,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'status' => 'payment_verification_pending',
                'notes' => "Customer submitted {$method} Transaction ID {$transactionId} for " . ($paymentType === 'delivery_advance' ? 'Advance Delivery Charge' : 'Full Order Payment') . ".",
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? $order->user_id,
                'action' => 'payment.submitted',
                'module' => 'payments',
                'record_id' => $payment->id,
                'new_values' => $payment->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Dispatch notification to admins
            $adminUsers = User::where('is_admin', true)->get();
            foreach ($adminUsers as $admin) {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\\Notifications\\ManualPaymentNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $admin->id,
                    'data' => json_encode([
                        'title' => "New " . strtoupper($method) . " " . ($paymentType === 'delivery_advance' ? 'Delivery Advance' : 'Full Payment'),
                        'message' => "Order #{$order->order_number} submitted Transaction ID {$transactionId} (" . format_price($expectedAmount) . ")",
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'url' => route('admin.payments.show', $payment->id),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Payment submitted successfully! Our team will verify your transaction shortly.',
                'payment' => $payment,
            ];
        });
    }

    public function verifyPayment(ManualPayment $payment, User $admin, ?string $adminNote = null): array
    {
        return DB::transaction(function () use ($payment, $admin, $adminNote) {
            $payment->update([
                'status' => 'verified',
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'admin_note' => $adminNote,
            ]);

            $order = $payment->order;

            if ($payment->payment_type === 'delivery_advance') {
                $advancePaid = (float) $payment->amount;
                $remainingDue = max(0, (float) $order->grand_total - $advancePaid);

                $order->update([
                    'status' => 'confirmed',
                    'payment_status' => 'partially_paid',
                    'delivery_advance_paid' => $advancePaid,
                    'remaining_amount' => $remainingDue,
                ]);
            } else {
                $order->update([
                    'status' => 'confirmed',
                    'payment_status' => 'verified',
                    'remaining_amount' => 0.00,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $admin->id,
                'status' => 'confirmed',
                'notes' => "Payment verified by Admin {$admin->name}. Transaction ID: {$payment->transaction_id}",
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'payment.verified',
                'module' => 'payments',
                'record_id' => $payment->id,
                'new_values' => $payment->fresh()->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify customer
            if ($order->user_id) {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\\Notifications\\ManualPaymentNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $order->user_id,
                    'data' => json_encode([
                        'title' => "Payment Verified ✓",
                        'message' => "Your payment of " . format_price($payment->amount) . " for Order #{$order->order_number} has been verified and confirmed!",
                        'order_id' => $order->id,
                        'url' => route('customer.orders.show', $order->id),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Payment verified successfully and order status updated to Confirmed.',
            ];
        });
    }

    public function rejectPayment(ManualPayment $payment, User $admin, string $reason, ?string $adminNote = null): array
    {
        return DB::transaction(function () use ($payment, $admin, $reason, $adminNote) {
            $payment->update([
                'status' => 'rejected',
                'rejected_by' => $admin->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'admin_note' => $adminNote,
            ]);

            $order = $payment->order;
            $order->update([
                'status' => 'payment_rejected',
                'payment_status' => 'rejected',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $admin->id,
                'status' => 'payment_rejected',
                'notes' => "Payment rejected by Admin {$admin->name}. Reason: {$reason}",
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'payment.rejected',
                'module' => 'payments',
                'record_id' => $payment->id,
                'new_values' => $payment->fresh()->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify customer with rejection reason
            if ($order->user_id) {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\\Notifications\\ManualPaymentNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $order->user_id,
                    'data' => json_encode([
                        'title' => "Payment Verification Rejected",
                        'message' => "Your payment for Order #{$order->order_number} was rejected. Reason: {$reason}",
                        'order_id' => $order->id,
                        'reason' => $reason,
                        'url' => route('customer.orders.show', $order->id),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Payment rejected successfully.',
            ];
        });
    }
}
