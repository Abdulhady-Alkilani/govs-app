<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->inquiries()->with('type');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('type', function($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sort') && $request->sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $inquiries = $query->paginate(10)->appends($request->query());
        
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
            'details' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $detailsContent = '';
        if ($request->has('custom_fields') && is_array($request->custom_fields)) {
            foreach ($request->custom_fields as $key => $value) {
                if (!empty($value)) {
                    $detailsContent .= "{$key}: {$value}\n";
                }
            }
        }
        if (!empty($request->details)) {
            if (!empty($detailsContent)) {
                $detailsContent .= "\nملاحظات إضافية:\n";
            }
            $detailsContent .= $request->details;
        }

        $inquiry = Inquiry::create([
            'citizen_id' => Auth::id(),
            'type_id' => $request->type_id,
            'details' => $detailsContent,
            'status' => 'pending',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('inquiry_attachments', 'public');
                $inquiry->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('inquiries.index')->with('success', 'تم إرسال طلب الاستعلام بنجاح وسيتم معالجته قريباً.');
    }

    public function show($id)
    {
        $inquiry = Auth::user()->inquiries()->with('type')->findOrFail($id);
        return view('inquiries.show', compact('inquiry'));
    }
}