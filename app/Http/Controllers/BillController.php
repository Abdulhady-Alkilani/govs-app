<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BillController extends Controller
{
    public function index()
    {
        $bills = Auth::user()->bills()->latest()->paginate(10);
        return view('bills.index', compact('bills'));
    }

    public function showPaymentForm($id)
    {
        $bill = Auth::user()->bills()->where('status', 'unpaid')->findOrFail($id);
        return view('bills.pay', compact('bill'));
    }

    public function processPayment(Request $request, $id)
    {
        $bill = Auth::user()->bills()->where('status', 'unpaid')->findOrFail($id);

        // محاكاة الاتصال ببوابة الدفع (شام كاش)
        // في الواقع الفعلي هنا يتم توجيه المستخدم لصفحة البنك، ثم نستقبل الرد.
        // للتبسيط والمحاكاة، سنعتبر أن الدفع تم بنجاح وسنولد رقم عملية عشوائي.
        
        $transactionId = 'SHAM-' . strtoupper(Str::random(10));

        $bill->update([
            'status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => $transactionId,
        ]);

        return redirect()->route('bills.index')->with('success', 'تم تسديد الفاتورة بنجاح. رقم العملية: ' . $transactionId);
    }
}