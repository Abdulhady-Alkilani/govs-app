<?php

use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintType;
use App\Models\Inquiry;
use App\Models\InquiryAttachment;
use App\Models\InquiryType;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->role = Role::create(['name' => 'citizen']);
    $this->user = User::factory()->create(['role_id' => $this->role->id]);
});

describe('Complaint Model', function () {
    it('creates complaint with fillable fields', function () {
        $type = ComplaintType::create(['name' => 'Test Type', 'is_active' => true]);

        $complaint = Complaint::create([
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'description' => 'Test complaint description',
            'status' => 'pending',
            'ai_priority' => 'high',
            'ai_summary' => 'AI generated summary',
        ]);

        expect($complaint)->toBeInstanceOf(Complaint::class)
            ->and($complaint->ai_priority)->toBe('high')
            ->and($complaint->ai_summary)->toBe('AI generated summary')
            ->and($complaint->status)->toBe('pending');
    });

    it('relates to citizen, type, assignee, and attachments', function () {
        $type = ComplaintType::create(['name' => 'Type', 'is_active' => true]);
        $employeeRole = Role::create(['name' => 'employee']);
        $employee = User::factory()->create(['role_id' => $employeeRole->id]);

        $complaint = Complaint::create([
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'description' => 'Test',
            'status' => 'pending',
            'assigned_to' => $employee->id,
        ]);

        expect($complaint->citizen->id)->toBe($this->user->id)
            ->and($complaint->type->id)->toBe($type->id)
            ->and($complaint->assignee->id)->toBe($employee->id);

        ComplaintAttachment::create([
            'complaint_id' => $complaint->id,
            'file_path' => 'test.pdf',
            'file_type' => 'application/pdf',
            'is_ai_verified' => true,
            'ai_ocr_text' => 'Extracted text',
        ]);

        expect($complaint->fresh()->attachments)->toHaveCount(1)
            ->and($complaint->fresh()->attachments->first()->is_ai_verified)->toBeTrue();
    });
});

describe('Inquiry Model', function () {
    it('creates inquiry with fillable fields', function () {
        $type = InquiryType::create(['name' => 'Inquiry Type', 'is_active' => true]);

        $inquiry = Inquiry::create([
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'details' => 'Some inquiry details',
            'status' => 'pending',
        ]);

        expect($inquiry)->toBeInstanceOf(Inquiry::class)
            ->and($inquiry->details)->toBe('Some inquiry details');
    });

    it('relates to attachments', function () {
        $type = InquiryType::create(['name' => 'Type', 'is_active' => true]);

        $inquiry = Inquiry::create([
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'status' => 'pending',
        ]);

        InquiryAttachment::create([
            'inquiry_id' => $inquiry->id,
            'file_path' => 'test.jpg',
            'file_name' => 'test.jpg',
            'file_type' => 'image/jpeg',
            'is_ai_verified' => false,
            'ai_ocr_text' => null,
        ]);

        expect($inquiry->fresh()->attachments)->toHaveCount(1);
    });
});

describe('ComplaintAttachment Model', function () {
    it('casts is_ai_verified to boolean', function () {
        $type = ComplaintType::create(['name' => 'T', 'is_active' => true]);
        $complaint = Complaint::create([
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'description' => 'desc',
            'status' => 'pending',
        ]);

        $attachment = ComplaintAttachment::create([
            'complaint_id' => $complaint->id,
            'file_path' => 'img.jpg',
            'file_type' => 'image/jpeg',
            'is_ai_verified' => 1,
            'ai_ocr_text' => 'text',
        ]);

        expect($attachment->fresh()->is_ai_verified)->toBeTrue();
    });
});

describe('InquiryAttachment Model', function () {
    it('casts is_ai_verified to boolean', function () {
        $type = InquiryType::create(['name' => 'T', 'is_active' => true]);
        $inquiry = Inquiry::create([
            'citizen_id' => $this->user->id,
            'type_id' => $type->id,
            'status' => 'pending',
        ]);

        $attachment = InquiryAttachment::create([
            'inquiry_id' => $inquiry->id,
            'file_path' => 'doc.png',
            'file_name' => 'doc.png',
            'file_type' => 'image/png',
            'is_ai_verified' => 0,
            'ai_ocr_text' => null,
        ]);

        expect($attachment->fresh()->is_ai_verified)->toBeFalse();
    });
});

describe('User Model', function () {
    it('has complaints and inquiries relationships', function () {
        $cType = ComplaintType::create(['name' => 'C', 'is_active' => true]);
        $iType = InquiryType::create(['name' => 'I', 'is_active' => true]);

        Complaint::create([
            'citizen_id' => $this->user->id,
            'type_id' => $cType->id,
            'description' => 'Test',
            'status' => 'pending',
        ]);

        Inquiry::create([
            'citizen_id' => $this->user->id,
            'type_id' => $iType->id,
            'status' => 'pending',
        ]);

        expect($this->user->complaints)->toHaveCount(1)
            ->and($this->user->inquiries)->toHaveCount(1);
    });

    it('accesses panel based on role', function () {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $panel = new class extends \Filament\Panel
        {
            public function __construct() {}

            public function getId(): string
            {
                return 'admin';
            }
        };

        expect($admin->canAccessPanel($panel))->toBeTrue()
            ->and($this->user->canAccessPanel($panel))->toBeFalse();
    });
});
