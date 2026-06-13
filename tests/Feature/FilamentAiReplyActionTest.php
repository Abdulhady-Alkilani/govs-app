<?php

use App\Filament\Resources\ComplaintResource\Pages\ListComplaints;
use App\Filament\Resources\InquiryResource\Pages\ListInquiries;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Inquiry;
use App\Models\InquiryType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;

use Livewire\Livewire;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => 'admin']);
    $this->citizenRole = Role::create(['name' => 'citizen']);
    $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
    $this->citizen = User::factory()->create(['role_id' => $this->citizenRole->id]);

    config([
        'ai.api_url' => 'https://fake-api.test/v1/chat/completions',
        'ai.api_key' => 'test-key',
        'ai.model' => 'test-model',
        'ai.max_tokens' => 100,
        'ai.temperature' => 0.5,
    ]);
});

describe('ComplaintResource generateAiReply action', function () {
    it('generates official reply and fills the official_reply field', function () {
        $type = ComplaintType::create(['name' => 'Infrastructure', 'is_active' => true]);
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $type->id,
            'description' => 'Test complaint description.',
            'status' => 'pending',
        ]);

        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Dear citizen, your request has been processed successfully.']]],
            ], 200),
        ]);

        $this->actingAs($this->admin);

        Livewire::test(ListComplaints::class)
            ->call('mountTableAction', 'changeStatusAndReply', $complaint->id)
            ->set('mountedTableActionsData.0.employee_quick_note', 'The request has been approved and processed.')
            ->call('mountTableAction', 'generateAiReply')
            ->assertSet('mountedTableActionsData.0.official_reply', 'Dear citizen, your request has been processed successfully.');
    });

    it('does not generate reply when quick note is too short', function () {
        $type = ComplaintType::create(['name' => 'Test', 'is_active' => true]);
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $type->id,
            'description' => 'Test complaint.',
            'status' => 'pending',
        ]);

        Http::fake(['*' => Http::response([], 200)]);

        $this->actingAs($this->admin);

        Livewire::test(ListComplaints::class)
            ->call('mountTableAction', 'changeStatusAndReply', $complaint->id)
            ->set('mountedTableActionsData.0.employee_quick_note', 'hi')
            ->call('mountTableAction', 'generateAiReply')
            ->assertSet('mountedTableActionsData.0.official_reply', null);
    });

    it('does not set reply when AI fails', function () {
        $type = ComplaintType::create(['name' => 'Test', 'is_active' => true]);
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $type->id,
            'description' => 'Test complaint.',
            'status' => 'pending',
        ]);

        Http::fake(['*' => Http::response(null, 500)]);

        $this->actingAs($this->admin);

        Livewire::test(ListComplaints::class)
            ->call('mountTableAction', 'changeStatusAndReply', $complaint->id)
            ->set('mountedTableActionsData.0.employee_quick_note', 'This is a valid quick note for testing.')
            ->call('mountTableAction', 'generateAiReply')
            ->assertSet('mountedTableActionsData.0.official_reply', null);
    });

    it('keeps the modal open after generating reply', function () {
        $type = ComplaintType::create(['name' => 'Test', 'is_active' => true]);
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $type->id,
            'description' => 'Test complaint.',
            'status' => 'pending',
        ]);

        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Generated reply text.']]],
            ], 200),
        ]);

        $this->actingAs($this->admin);

        Livewire::test(ListComplaints::class)
            ->call('mountTableAction', 'changeStatusAndReply', $complaint->id)
            ->set('mountedTableActionsData.0.employee_quick_note', 'Valid note for generating reply.')
            ->call('mountTableAction', 'generateAiReply')
            ->assertSet('mountedTableActions', ['changeStatusAndReply']);
    });
});

describe('InquiryResource generateAiReply action', function () {
    it('generates official reply and fills the official_reply field', function () {
        $type = InquiryType::create(['name' => 'General', 'is_active' => true]);
        $inquiry = Inquiry::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $type->id,
            'status' => 'pending',
        ]);

        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'Dear citizen, your inquiry has been answered.']]],
            ], 200),
        ]);

        $this->actingAs($this->admin);

        Livewire::test(ListInquiries::class)
            ->call('mountTableAction', 'changeStatusAndReply', $inquiry->id)
            ->set('mountedTableActionsData.0.employee_quick_note', 'The inquiry result is ready and approved.')
            ->call('mountTableAction', 'generateAiReply')
            ->assertSet('mountedTableActionsData.0.official_reply', 'Dear citizen, your inquiry has been answered.');
    });
});
