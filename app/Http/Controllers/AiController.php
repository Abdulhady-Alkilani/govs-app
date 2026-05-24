<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    /**
     * توليد رد رسمي من ملاحظة الموظف
     * POST /ai/generate-reply
     */
    public function generateReply(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('generateReply hit!', ['quick_note' => $request->quick_note, 'user' => auth()->id()]);
        $request->validate([
            'quick_note' => 'required|string|min:5',
        ]);

        $reply = AiService::generateOfficialReply($request->quick_note);

        if (!$reply) {
            return response()->json([
                'success' => false,
                'message' => __('حدث خطأ أثناء توليد الرد. يرجى المحاولة مرة أخرى.'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }

    /**
     * تحسين صياغة نص الشكوى
     * POST /ai/enhance-text
     */
    public function enhanceText(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:10',
        ]);

        $enhanced = AiService::enhanceText($request->text);

        if (!$enhanced) {
            return response()->json([
                'success' => false,
                'message' => __('حدث خطأ أثناء تحسين النص. يرجى المحاولة مرة أخرى.'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'enhanced_text' => $enhanced,
        ]);
    }
}
