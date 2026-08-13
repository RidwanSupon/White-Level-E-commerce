<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Services\ManualPaymentService;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = ManualPayment::with(['order', 'user', 'verifier', 'rejecter'])->latest();

        if (in_array($status, ['verification_pending', 'verified', 'rejected'])) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(15)->withQueryString();

        // Dashboard Metrics
        $counts = [
            'all' => ManualPayment::count(),
            'verification_pending' => ManualPayment::where('status', 'verification_pending')->count(),
            'verified' => ManualPayment::where('status', 'verified')->count(),
            'rejected' => ManualPayment::where('status', 'rejected')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'counts', 'status'));
    }

    public function show($id)
    {
        $payment = ManualPayment::with(['order.items.product', 'user', 'verifier', 'rejecter'])->findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, $id, ManualPaymentService $manualPaymentService)
    {
        $payment = ManualPayment::findOrFail($id);

        if ($payment->status === 'verified') {
            return back()->with('error', 'This payment has already been verified.');
        }

        $result = $manualPaymentService->verifyPayment($payment, auth()->user(), $request->input('admin_note'));

        return redirect()->route('admin.payments.index')->with('success', $result['message']);
    }

    public function reject(Request $request, $id, ManualPaymentService $manualPaymentService)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = ManualPayment::findOrFail($id);

        if ($payment->status === 'rejected') {
            return back()->with('error', 'This payment has already been rejected.');
        }

        $result = $manualPaymentService->rejectPayment(
            $payment,
            auth()->user(),
            $validated['rejection_reason'],
            $validated['admin_note'] ?? null
        );

        return redirect()->route('admin.payments.index')->with('success', $result['message']);
    }
}
