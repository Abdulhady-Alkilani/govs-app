<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->bills();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhere('bill_type', 'like', '%' . $request->search . '%')
                  ->orWhere('transaction_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sort')) {
            if ($request->sort === 'oldest') {
                $query->oldest('created_at');
            } elseif ($request->sort === 'latest') {
                $query->latest('created_at');
            } elseif ($request->sort === 'due_soon') {
                $query->orderBy('due_date', 'asc');
            }
        } else {
            $query->latest('created_at');
        }

        $bills = $query->paginate(10)->appends($request->query());
        
        return view('bills.index', compact('bills'));
    }

    public function create()
    {
        return view('bills.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bill_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
        ]);

        Auth::user()->bills()->create([
            'bill_type' => $request->bill_type,
            'amount' => $request->amount,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30),
        ]);

        return redirect()->route('bills.index')->with('success', 'تم تقديم الفاتورة بنجاح. يمكنك الآن تسديدها.');
    }

    public function showPaymentForm($id)
    {
        $bill = Auth::user()->bills()->where('status', 'unpaid')->findOrFail($id);
        return view('bills.pay', compact('bill'));
    }

    public function processPayment(Request $request, $id)
    {
        $bill = Auth::user()->bills()->where('status', 'unpaid')->findOrFail($id);

        $request->validate([
            'paid_amount' => 'required|numeric|min:1',
            'payment_receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'payment_details' => 'nullable|string',
        ]);

        $receiptPath = $request->file('payment_receipt')->store('receipts', 'public');

        $transactionId = 'PAY-' . strtoupper(Str::random(10));

        $bill->update([
            'status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => $transactionId,
            'paid_amount' => $request->paid_amount,
            'payment_receipt_path' => $receiptPath,
            'payment_details' => $request->payment_details,
        ]);

        return redirect()->route('bills.index')->with('success', 'تم تسديد الفاتورة وإرفاق الإشعار بنجاح. رقم العملية: ' . $transactionId);
    }

    public function show($id)
    {
        $bill = Auth::user()->bills()->findOrFail($id);
        return view('bills.show', compact('bill'));
    }
}