<?php

use App\Models\ComplaintType;
use App\Models\InquiryType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->role = Role::create(['name' => 'citizen']);
    $this->user = User::factory()->create(['role_id' => $this->role->id]);
});

describe('ComplaintController', function () {
    it('displays complaints index for authenticated user', function () {
        $response = $this->actingAs($this->user)->get('/complaints');

        $response->assertStatus(200);
    });

    it('displays complaint creation form', function () {
        $response = $this->actingAs($this->user)->get('/complaints/create');

        $response->assertStatus(200);
    });

    it('stores a complaint without attachments', function () {
        $type = ComplaintType::create(['name' => 'Infrastructure', 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"summary":"Test summary","priority":"medium"}']]],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post('/complaints', [
            'type_id' => $type->id,
            'description' => 'This is a test complaint that is long enough to pass validation.',
        ]);

        $response->assertRedirect('/complaints');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'ai_priority' => 'medium',
            'ai_summary' => 'Test summary',
        ]);
    });

    it('stores complaint even when AI classification fails', function () {
        $type = ComplaintType::create(['name' => 'Test', 'is_active' => true]);

        Http::fake(['*' => Http::response(null, 500)]);

        $response = $this->actingAs($this->user)->post('/complaints', [
            'type_id' => $type->id,
            'description' => 'This is a test complaint that is long enough.',
        ]);

        $response->assertRedirect('/complaints');

        $this->assertDatabaseHas('complaints', [
            'citizen_id' => $this->user->id,
            'ai_priority' => null,
            'ai_summary' => null,
        ]);
    });

    it('validates required fields on store', function () {
        $response = $this->actingAs($this->user)->post('/complaints', []);

        $response->assertSessionHasErrors(['type_id', 'description']);
    });

    it('validates description minimum length', function () {
        $type = ComplaintType::create(['name' => 'T', 'is_active' => true]);

        $response = $this->actingAs($this->user)->post('/complaints', [
            'type_id' => $type->id,
            'description' => 'short',
        ]);

        $response->assertSessionHasErrors(['description']);
    });

    it('validates attachment file types', function () {
        $type = ComplaintType::create(['name' => 'T', 'is_active' => true]);
        $file = UploadedFile::fake()->create('document.exe', 100);

        $response = $this->actingAs($this->user)->post('/complaints', [
            'type_id' => $type->id,
            'description' => 'A valid complaint description here.',
            'attachments' => [$file],
        ]);

        $response->assertSessionHasErrors(['attachments.0']);
    });

    it('shows a specific complaint to its owner', function () {
        $type = ComplaintType::create(['name' => 'T', 'is_active' => true]);
        $complaint = $this->user->complaints()->create([
            'type_id' => $type->id,
            'description' => 'Test complaint',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get("/complaints/{$complaint->id}");

        $response->assertStatus(200);
    });

    it('prevents user from viewing other users complaint', function () {
        $otherUser = User::factory()->create(['role_id' => $this->role->id]);
        $type = ComplaintType::create(['name' => 'T', 'is_active' => true]);
        $complaint = $otherUser->complaints()->create([
            'type_id' => $type->id,
            'description' => 'Other complaint',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get("/complaints/{$complaint->id}");

        $response->assertStatus(404);
    });

    it('stores complaint with image attachment and runs AI verification', function () {
        $type = ComplaintType::create(['name' => 'T', 'is_active' => true]);
        $file = UploadedFile::fake()->create('id_card.jpg', 100, 'image/jpeg');

        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"summary":"S","priority":"low"}']]],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post('/complaints', [
            'type_id' => $type->id,
            'description' => 'A valid complaint description here.',
            'attachments' => [$file],
        ]);

        $response->assertRedirect('/complaints');

        $this->assertDatabaseHas('complaint_attachments', [
            'file_type' => 'image/jpeg',
        ]);
    });
});

describe('InquiryController', function () {
    it('displays inquiries index for authenticated user', function () {
        $response = $this->actingAs($this->user)->get('/inquiries');

        $response->assertStatus(200);
    });

    it('displays inquiry creation form', function () {
        $response = $this->actingAs($this->user)->get('/inquiries/create');

        $response->assertStatus(200);
    });

    it('stores an inquiry without attachments', function () {
        $type = InquiryType::create(['name' => 'General', 'is_active' => true]);

        $response = $this->actingAs($this->user)->post('/inquiries', [
            'type_id' => $type->id,
        ]);

        $response->assertRedirect('/inquiries');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'status' => 'pending',
        ]);
    });

    it('stores inquiry with custom fields', function () {
        $type = InquiryType::create(['name' => 'T', 'is_active' => true]);

        $response = $this->actingAs($this->user)->post('/inquiries', [
            'type_id' => $type->id,
            'custom_fields' => [
                'full_name' => 'Ahmed',
                'id_number' => '12345',
            ],
            'details' => 'Additional notes here.',
        ]);

        $response->assertRedirect('/inquiries');

        $this->assertDatabaseHas('inquiries', [
            'citizen_id' => $this->user->id,
        ]);
    });

    it('validates type_id is required', function () {
        $response = $this->actingAs($this->user)->post('/inquiries', []);

        $response->assertSessionHasErrors(['type_id']);
    });

    it('shows a specific inquiry to its owner', function () {
        $type = InquiryType::create(['name' => 'T', 'is_active' => true]);
        $inquiry = $this->user->inquiries()->create([
            'type_id' => $type->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get("/inquiries/{$inquiry->id}");

        $response->assertStatus(200);
    });

    it('prevents user from viewing other users inquiry', function () {
        $otherUser = User::factory()->create(['role_id' => $this->role->id]);
        $type = InquiryType::create(['name' => 'T', 'is_active' => true]);
        $inquiry = $otherUser->inquiries()->create([
            'type_id' => $type->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get("/inquiries/{$inquiry->id}");

        $response->assertStatus(404);
    });

    it('stores inquiry with image attachment and verifies via AI', function () {
        Storage::fake('public');
        $type = InquiryType::create(['name' => 'T', 'is_active' => true]);
        $file = UploadedFile::fake()->create('doc.jpg', 100, 'image/jpeg');

        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '{"is_valid":true,"extracted_text":"Document text"}']]],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post('/inquiries', [
            'type_id' => $type->id,
            'attachments' => [$file],
        ]);

        $response->assertRedirect('/inquiries');

        $this->assertDatabaseHas('inquiry_attachments', [
            'is_ai_verified' => true,
            'ai_ocr_text' => 'Document text',
        ]);
    });
});

describe('AiController', function () {
    it('generates official reply successfully', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Dear citizen, your matter is resolved.']]],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/generate-reply', [
            'quick_note' => 'Approved the request',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['reply']);
    });

    it('validates quick_note is required for generate-reply', function () {
        $response = $this->actingAs($this->user)->postJson('/ai/generate-reply', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quick_note']);
    });

    it('validates quick_note minimum length', function () {
        $response = $this->actingAs($this->user)->postJson('/ai/generate-reply', [
            'quick_note' => 'Hi',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quick_note']);
    });

    it('returns 500 when AI fails for generate-reply', function () {
        Http::fake(['*' => Http::response(null, 500)]);

        $response = $this->actingAs($this->user)->postJson('/ai/generate-reply', [
            'quick_note' => 'This is a valid note for testing',
        ]);

        $response->assertStatus(500)
            ->assertJson(['success' => false]);
    });

    it('enhances text successfully', function () {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Enhanced formal text.']]],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/enhance-text', [
            'text' => 'This is a test text that needs enhancement.',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['enhanced_text']);
    });

    it('validates text is required for enhance-text', function () {
        $response = $this->actingAs($this->user)->postJson('/ai/enhance-text', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    });

    it('validates text minimum length for enhance-text', function () {
        $response = $this->actingAs($this->user)->postJson('/ai/enhance-text', [
            'text' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    });

    it('returns 500 when AI fails for enhance-text', function () {
        Http::fake(['*' => Http::response(null, 500)]);

        $response = $this->actingAs($this->user)->postJson('/ai/enhance-text', [
            'text' => 'This is a long enough text for the validation to pass.',
        ]);

        $response->assertStatus(500)
            ->assertJson(['success' => false]);
    });
});

describe('Authentication & Authorization', function () {
    it('redirects unauthenticated users from complaints', function () {
        $response = $this->get('/complaints');

        $response->assertRedirect('/login');
    });

    it('redirects unauthenticated users from inquiries', function () {
        $response = $this->get('/inquiries');

        $response->assertRedirect('/login');
    });

    it('redirects unauthenticated users from AI endpoints', function () {
        $response = $this->postJson('/ai/generate-reply', [
            'quick_note' => 'Test note for validation',
        ]);

        $response->assertStatus(401);
    });
});
