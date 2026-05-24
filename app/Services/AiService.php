<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * إرسال نص للذكاء الاصطناعي والحصول على الرد
     */
    public static function chat(string $prompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'x-litellm-api-key' => config('ai.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(config('ai.api_url'), [
                'model' => config('ai.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => (int) config('ai.max_tokens'),
                'temperature' => (float) config('ai.temperature'),
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('AI API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AI Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * إرسال نص + صورة للذكاء الاصطناعي (Vision AI)
     */
    public static function chatWithImage(string $prompt, string $base64Image, string $mimeType): ?string
    {
        try {
            $response = Http::withHeaders([
                'x-litellm-api-key' => config('ai.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(config('ai.api_url'), [
                'model' => config('ai.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}",
                                ],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => (int) config('ai.max_tokens'),
                'temperature' => (float) config('ai.temperature'),
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('AI Vision API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AI Vision Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * تحليل وتصنيف شكوى — يعيد مصفوفة ['summary' => ..., 'priority' => ...]
     */
    public static function classifyComplaint(string $description): ?array
    {
        $prompt = "قم بتحليل هذه الشكوى: '{$description}'. أعد لي استجابة بصيغة JSON فقط تحتوي على مفتاحين: 'summary' (تلخيص للمشكلة في سطر واحد)، و 'priority' (إما 'high' أو 'medium' أو 'low' بناءً على مدى خطورة المشكلة). لا تضف أي نص خارج JSON.";

        $response = self::chat($prompt);

        if (!$response) {
            return null;
        }

        // تنظيف الرد من أي نصوص إضافية واستخراج JSON فقط
        $cleaned = preg_replace('/```json\s*/', '', $response);
        $cleaned = preg_replace('/```\s*/', '', $cleaned);
        $cleaned = trim($cleaned);

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('AI Classification: Invalid JSON response', [
                'response' => $response,
            ]);
            return null;
        }

        // التأكد من وجود المفاتيح المطلوبة
        if (!isset($data['summary']) || !isset($data['priority'])) {
            Log::warning('AI Classification: Missing required keys', [
                'data' => $data,
            ]);
            return null;
        }

        // التأكد من صحة قيمة الأولوية
        if (!in_array($data['priority'], ['high', 'medium', 'low'])) {
            $data['priority'] = 'medium';
        }

        return $data;
    }

    /**
     * توليد رد رسمي من ملاحظة الموظف
     */
    public static function generateOfficialReply(string $quickNote): ?string
    {
        $prompt = "كممثل رسمي لجهة حكومية، قم بتحويل هذه الملاحظة القصيرة: '{$quickNote}' إلى رسالة رسمية، مهذبة، ومختصرة لترسل للمواطن بخصوص معاملته. لا تضف أي معلومات غير موجودة في الملاحظة الأصلية.";

        return self::chat($prompt);
    }

    /**
     * تحسين صياغة نص الشكوى
     */
    public static function enhanceText(string $text): ?string
    {
        $prompt = "قم بتحسين وصياغة النص التالي ليكون شكوى رسمية موجهة لجهة حكومية. صحح الأخطاء الإملائية، واجعله واضحاً، مهذباً، ومباشراً. أعد النص المحسن فقط بدون أي إضافات: '{$text}'.";

        return self::chat($prompt);
    }

    /**
     * التحقق من المرفقات باستخدام Vision AI
     * يعيد مصفوفة ['is_valid' => bool, 'extracted_text' => string]
     */
    public static function verifyAttachment(string $base64Image, string $mimeType): ?array
    {
        $prompt = "هل هذه الصورة عبارة عن وثيقة رسمية، هوية شخصية، أو فاتورة؟ إذا نعم، أعد JSON يحتوي على 'is_valid': true و 'extracted_text': 'النص الموجود في الصورة'. إذا كانت صورة عشوائية (مثلاً سيلفي أو منظر طبيعي)، أعد 'is_valid': false و 'extracted_text': ''. أعد JSON فقط بدون أي نص إضافي.";

        $response = self::chatWithImage($prompt, $base64Image, $mimeType);

        if (!$response) {
            return null;
        }

        // تنظيف الرد واستخراج JSON
        $cleaned = preg_replace('/```json\s*/', '', $response);
        $cleaned = preg_replace('/```\s*/', '', $cleaned);
        $cleaned = trim($cleaned);

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('AI Verification: Invalid JSON response', [
                'response' => $response,
            ]);
            return null;
        }

        return [
            'is_valid' => $data['is_valid'] ?? false,
            'extracted_text' => $data['extracted_text'] ?? '',
        ];
    }
}
