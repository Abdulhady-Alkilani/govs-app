<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * إرسال نص للذكاء الاصطناعي والحصول على الرد
     */
    public static function chat(string $prompt): ?string
    {
        set_time_limit(300);

        try {
            $response = Http::withHeaders([
                'x-litellm-api-key' => config('ai.api_key'),
                'Content-Type' => 'application/json',
            ])
                ->connectTimeout((int) config('ai.connect_timeout', 30))
                ->timeout((int) config('ai.timeout', 180))
                ->post(config('ai.api_url'), [
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
        } catch (ConnectionException $e) {
            Log::error('AI Service: انتهت مهلة الاتصال (timeout)', [
                'message' => $e->getMessage(),
            ]);

            throw new \Exception('انتهت مهلة الاتصال بخدمات الذكاء الاصطناعي بسبب بطء الإنترنت. يرجى المحاولة مرة أخرى.');
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
        set_time_limit(300);

        try {
            $response = Http::withHeaders([
                'x-litellm-api-key' => config('ai.api_key'),
                'Content-Type' => 'application/json',
            ])
                ->connectTimeout((int) config('ai.connect_timeout', 30))
                ->timeout((int) config('ai.vision_timeout', 300))
                ->post(config('ai.api_url'), [
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
        } catch (ConnectionException $e) {
            Log::error('AI Vision Service: انتهت مهلة الاتصال (timeout)', [
                'message' => $e->getMessage(),
            ]);

            throw new \Exception('انتهت مهلة الاتصال بخدمات الذكاء الاصطناعي بسبب بطء الإنترنت. يرجى المحاولة مرة أخرى.');
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
    public static function classifyComplaint(string $description, ?string $typeName = null): ?array
    {
        $typeContext = $typeName ? "نوع الشكوى: '{$typeName}'." : '';

        $prompt = <<<PROMPT
        أنت نظام تصنيف شكاوى حكومية. {$typeContext}
        قم بتحليل هذه الشكوى: '{$description}'.

        صنّف الأولوية وفق المعايير التالية بدقة:
        - 'high' (عالية): خطر مباشر على الحياة أو الصحة العامة، انقطاع كامل لخدمة أساسية (كهرباء/مياه) عن منطقة، فيضان صرف صحي، تلوث بيئي خطير، أو حالة طوارئ.
        - 'medium' (متوسطة): مشاكل خدمية متكررة أو مستمرة، انقطاع جزئي في الخدمات، تأخير ملحوظ، أو مشكلة تؤثر على مجموعة من السكان.
        - 'low' (منخفضة): استفسارات عامة، طلبات تحسين، شكاوى تجميلية، أو مشاكل بسيطة لا تؤثر على الخدمات الأساسية.

        أعد لي استجابة بصيغة JSON فقط تحتوي على مفتاحين:
        - 'summary': تلخيص للمشكلة في سطر واحد
        - 'priority': إما 'high' أو 'medium' أو 'low'
        لا تضف أي نص خارج JSON.
        PROMPT;

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
        $prompt = <<<PROMPT
        أنت نظام مراسلات حكومية رسمية. اكتب رداً موجهاً مباشرةً للمواطن بناءً على هذه الملاحظة الداخلية للموظف: "{$quickNote}"

        التعليمات الإلزامية:
        - ابدأ الرسالة مباشرةً بالتحية الرسمية للمواطن (مثل: "المواطن الكريم،" أو "تحية طيبة وبعد،").
        - لا تكتب أي مقدمة أو توضيح أو تعليق على صياغة الرسالة (لا تكتب: "إليك صياغة"، "هذا رد"، "الرد الرسمي هو"، إلخ).
        - لا تذكر أن النص مُولّد أو مُعاد صياغته.
        - لا تضف أي معلومات أو تفاصيل غير موجودة في الملاحظة الأصلية.
        - اجعل الصيغة رسمية، مهذبة، ومختصرة.
        - أعد نص الرسالة الجاهزة للإرسال فقط.
        PROMPT;

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
     * يعيد مصفوفة ['is_valid' => bool, 'extracted_text' => string, 'rejection_reason' => string]
     */
    public static function verifyAttachment(string $base64Image, string $mimeType): ?array
    {
        $prompt = <<<PROMPT
        أنت نظام فحص وثائق حكومي صارم. افحص هذه الصورة وحدد إذا كانت وثيقة رسمية مقبولة أم لا.

        الوثائق المقبولة فقط:
        - هوية شخصية أو بطاقة وطنية
        - جواز سفر
        - فاتورة خدمات (كهرباء، مياه، هاتف)
        - وثيقة حكومية مختومة أو موقعة
        - إيصال رسمي
        - شهادة رسمية (ميلاد، زواج، وفاة)
        - سند ملكية أو عقد رسمي

        الوثائق المرفوضة:
        - أغلفة كتب أو مذكرات دراسية أو محاضرات
        - صور شخصية (سيلفي) أو صور عامة
        - مناظر طبيعية أو صور عشوائية
        - لقطات شاشة من مواقع أو تطبيقات
        - أي صورة ليست وثيقة رسمية حكومية أو مالية

        أعد JSON فقط يحتوي على:
        - 'is_valid': true إذا كانت وثيقة رسمية مقبولة، false إذا لم تكن
        - 'extracted_text': النص المستخرج من الوثيقة (فارغ إذا رُفضت)
        - 'rejection_reason': سبب الرفض باللغة العربية (فارغ إذا قُبلت)
        لا تضف أي نص خارج JSON.
        PROMPT;

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
            'rejection_reason' => $data['rejection_reason'] ?? '',
        ];
    }
}
