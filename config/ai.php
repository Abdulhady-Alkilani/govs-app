<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to the LiteLLM proxy API.
    | The proxy routes requests to the appropriate model provider.
    |
    */

    'api_url'     => env('AI_API_URL'),
    'api_key'     => env('AI_API_KEY'),
    'model'       => env('AI_MODEL', 'gemini-3-flash-preview'),
    'max_tokens'  => env('AI_MAX_TOKENS', 4096),
    'temperature' => env('AI_TEMPERATURE', 0.7),

    /*
    |--------------------------------------------------------------------------
    | Timeouts (seconds)
    |--------------------------------------------------------------------------
    |
    | مهلات الاتصال والاستجابة لخدمات الذكاء الاصطناعي. تُرفع هذه القيم
    | لأن خدمات الذكاء قد تستغرق وقتًا أطول حسب سرعة الشبكة وحجم الطلب.
    |
    */

    'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', 30),
    'timeout'         => (int) env('AI_TIMEOUT', 180),
    'vision_timeout'  => (int) env('AI_VISION_TIMEOUT', 300),
];
