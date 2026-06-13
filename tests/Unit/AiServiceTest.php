<?php

use App\Services\AiService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'ai.api_url' => 'https://fake-api.test/v1/chat/completions',
        'ai.api_key' => 'test-key',
        'ai.model' => 'test-model',
        'ai.max_tokens' => 100,
        'ai.temperature' => 0.5,
    ]);
});

describe('AiService::chat', function () {
    it('sends prompt and returns response content', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Hello from AI']]],
            ], 200),
        ]);

        $result = AiService::chat('test prompt');

        expect($result)->toBe('Hello from AI');

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-litellm-api-key', 'test-key')
                && $request['model'] === 'test-model'
                && $request['messages'][0]['content'] === 'test prompt';
        });
    });

    it('returns null on API error', function () {
        Http::fake(['*' => Http::response(['error' => 'fail'], 500)]);

        $result = AiService::chat('test');

        expect($result)->toBeNull();
    });

    it('returns null on exception', function () {
        Http::fake(function () {
            throw new \Exception('Connection failed');
        });

        $result = AiService::chat('test');

        expect($result)->toBeNull();
    });
});

describe('AiService::classifyComplaint', function () {
    it('parses valid JSON response and returns array', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"summary":"Test summary","priority":"high"}']]],
            ], 200),
        ]);

        $result = AiService::classifyComplaint('A long complaint text');

        expect($result)->toBe(['summary' => 'Test summary', 'priority' => 'high']);
    });

    it('handles markdown-wrapped JSON', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => "```json\n{\"summary\":\"S\",\"priority\":\"low\"}\n```"]]],
            ], 200),
        ]);

        $result = AiService::classifyComplaint('complaint');

        expect($result)->toBe(['summary' => 'S', 'priority' => 'low']);
    });

    it('defaults invalid priority to medium', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"summary":"S","priority":"critical"}']]],
            ], 200),
        ]);

        $result = AiService::classifyComplaint('complaint');

        expect($result['priority'])->toBe('medium');
    });

    it('returns null for invalid JSON', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'not json at all']]],
            ], 200),
        ]);

        $result = AiService::classifyComplaint('complaint');

        expect($result)->toBeNull();
    });

    it('returns null when summary key is missing', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"priority":"high"}']]],
            ], 200),
        ]);

        $result = AiService::classifyComplaint('complaint');

        expect($result)->toBeNull();
    });

    it('returns null when chat returns null', function () {
        Http::fake(['*' => Http::response(null, 500)]);

        $result = AiService::classifyComplaint('complaint');

        expect($result)->toBeNull();
    });
});

describe('AiService::generateOfficialReply', function () {
    it('returns AI response text', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Dear citizen, your request has been processed.']]],
            ], 200),
        ]);

        $result = AiService::generateOfficialReply('approved');

        expect($result)->toBe('Dear citizen, your request has been processed.');
    });
});

describe('AiService::enhanceText', function () {
    it('returns enhanced text from AI', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Enhanced complaint text']]],
            ], 200),
        ]);

        $result = AiService::enhanceText('raw complaint');

        expect($result)->toBe('Enhanced complaint text');
    });
});

describe('AiService::verifyAttachment', function () {
    it('parses valid verification response', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"is_valid":true,"extracted_text":"ID Number: 12345"}']]],
            ], 200),
        ]);

        $result = AiService::verifyAttachment('base64data', 'image/jpeg');

        expect($result)->toBe(['is_valid' => true, 'extracted_text' => 'ID Number: 12345']);
    });

    it('defaults is_valid to false when missing', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"extracted_text":"some text"}']]],
            ], 200),
        ]);

        $result = AiService::verifyAttachment('base64data', 'image/png');

        expect($result['is_valid'])->toBeFalse();
    });

    it('defaults extracted_text to empty string when missing', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"is_valid":true}']]],
            ], 200),
        ]);

        $result = AiService::verifyAttachment('base64data', 'image/png');

        expect($result['extracted_text'])->toBe('');
    });

    it('returns null on invalid JSON response', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'not json']]],
            ], 200),
        ]);

        $result = AiService::verifyAttachment('base64data', 'image/png');

        expect($result)->toBeNull();
    });

    it('sends image data in correct format', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"is_valid":true,"extracted_text":""}']]],
            ], 200),
        ]);

        AiService::verifyAttachment('abc123', 'image/png');

        Http::assertSent(function ($request) {
            $content = $request['messages'][0]['content'];

            return is_array($content)
                && $content[0]['type'] === 'text'
                && $content[1]['type'] === 'image_url'
                && $content[1]['image_url']['url'] === 'data:image/png;base64,abc123';
        });
    });
});
