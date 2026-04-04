<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    public function index()
    {
        $inquiries = Auth::user()->inquiries()->with('type')->latest()->paginate(10);
        return view('inquiries.index', compact('inquiries'));
    }

    public function create()
    {
        $types = InquiryType::where('is_active', true)->get();
        return view('inquiries.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:inquiry_types,id',
        ]);

        Inquiry::create([
            'citizen_id' => Auth::id(),
            'type_id' => $request->type_id,
            'status' => 'pending',
        ]);

        return redirect()->route('inquiries.index')->with('success', 'تم إرسال طلب الاستعلام بنجاح وسيتم معالجته قريباً.');
    }

    public function show($id)
    {
        $inquiry = Auth::user()->inquiries()->with('type')->findOrFail($id);
        return view('inquiries.show', compact('inquiry'));
    }
}