<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\ComplaintAttachment;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->complaints()->with('type');

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

        $complaints = $query->paginate(10)->appends($request->query());
        
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

        // ===== الميزة 1: التصنيف التلقائي بالذكاء الاصطناعي =====
        try {
            $classification = AiService::classifyComplaint($request->description);
            if ($classification) {
                $complaint->update([
                    'ai_summary' => $classification['summary'],
                    'ai_priority' => $classification['priority'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('AI Classification failed for complaint #' . $complaint->id, [
                'error' => $e->getMessage(),
            ]);
            // لا نوقف عملية الحفظ الأساسية
        }

        // معالجة المرفقات إن وجدت
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaints_attachments', 'public');
                $mimeType = $file->getClientMimeType();
                
                $attachment = ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'file_path' => $path,
                    'file_type' => $mimeType,
                ]);

                // ===== الميزة 6: التحقق من المرفقات بالرؤية (Vision AI) =====
                try {
                    $isImage = in_array(
                        strtolower($file->getClientOriginalExtension()),
                        ['jpg', 'jpeg', 'png']
                    );

                    if ($isImage) {
                        $base64Image = base64_encode(file_get_contents($file->getRealPath()));
                        $verification = AiService::verifyAttachment($base64Image, $mimeType);

                        if ($verification) {
                            $attachment->update([
                                'is_ai_verified' => $verification['is_valid'],
                                'ai_ocr_text' => $verification['extracted_text'] ?? null,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('AI Verification failed for attachment #' . $attachment->id, [
                        'error' => $e->getMessage(),
                    ]);
                    // لا نوقف عملية الحفظ الأساسية
                }
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