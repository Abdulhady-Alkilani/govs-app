<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\ComplaintAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function index()
    {
        // جلب شكاوى المواطن المسجل فقط
        $complaints = Auth::user()->complaints()->with('type')->latest()->paginate(10);
        return view('complaints.index', compact('complaints'));
    }

    public function create()
    {
        // جلب أنواع الشكاوى المفعلة فقط من الإدارة
        $types = ComplaintType::where('is_active', true)->get();
        return view('complaints.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:complaint_types,id',
            'description' => 'required|string|min:10',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // حد أقصى 5 ميغا
        ]);

        // إنشاء الشكوى
        $complaint = Complaint::create([
            'citizen_id' => Auth::id(),
            'type_id' => $request->type_id,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        // معالجة المرفقات إن وجدت
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaints_attachments', 'public');
                
                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('complaints.index')->with('success', 'تم تقديم الشكوى بنجاح.');
    }

    public function show($id)
    {
        // التأكد من أن الشكوى تعود للمواطن نفسه
        $complaint = Auth::user()->complaints()->with(['type', 'attachments'])->findOrFail($id);
        return view('complaints.show', compact('complaint'));
    }
}