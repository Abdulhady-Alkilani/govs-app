<?php

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Inquiry;
use App\Models\InquiryType;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => 'admin']);
    $this->employeeRole = Role::create(['name' => 'employee']);
    $this->citizenRole = Role::create(['name' => 'citizen']);

    $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
    $this->employee = User::factory()->create(['role_id' => $this->employeeRole->id]);
    $this->citizen = User::factory()->create(['role_id' => $this->citizenRole->id]);

    $this->complaintType = ComplaintType::create(['name' => 'Test Type', 'is_active' => true]);
    $this->inquiryType = InquiryType::create(['name' => 'Test Inquiry Type', 'is_active' => true]);

    Http::fake([
        '*' => Http::response([
            'choices' => [['message' => ['content' => '{"summary":"S","priority":"medium"}']]],
        ], 200),
    ]);
});

describe('NotificationService', function () {
    it('sends Filament notification to user synchronously', function () {
        $this->assertDatabaseCount('notifications', 0);

        NotificationService::sendToUser(
            $this->admin,
            'Test Title',
            'Test Body',
            'heroicon-o-bell',
            'info'
        );

        $this->assertDatabaseCount('notifications', 1);
        $notification = \DB::table('notifications')->first();
        $data = json_decode($notification->data, true);
        expect($data['title'])->toBe('Test Title');
        expect($data['body'])->toBe('Test Body');
        expect($data['format'])->toBe('filament');
        expect($notification->notifiable_id)->toBe($this->admin->id);
        expect($notification->notifiable_type)->toBe(User::class);
    });

    it('sends custom notification record alongside Filament notification', function () {
        $initialCustom = \DB::table('custom_notifications')->count();

        NotificationService::sendToUser(
            $this->admin,
            'Custom Title',
            'Custom Body',
            'heroicon-o-bell',
            'info',
            '/admin/test'
        );

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('custom_notifications', $initialCustom + 1);

        $latestCustom = \DB::table('custom_notifications')->latest()->first();
        expect($latestCustom->user_id)->toBe($this->admin->id);
        expect($latestCustom->title)->toBe('Custom Title');
        expect($latestCustom->action_url)->toBe('/admin/test');
        expect($latestCustom->is_read)->toBe(0);
    });

    it('sends notification to multiple users', function () {
        $admin2 = User::factory()->create(['role_id' => $this->adminRole->id]);

        $this->assertDatabaseCount('notifications', 0);

        NotificationService::sendToUsers(
            collect([$this->admin, $admin2]),
            'Multi Title',
            'Multi Body',
            'heroicon-o-star',
            'success'
        );

        $this->assertDatabaseCount('notifications', 2);
    });

    it('sends notification with action URL', function () {
        NotificationService::sendToUser(
            $this->employee,
            'Assigned Item',
            'An item has been assigned to you',
            'heroicon-o-document-text',
            'info',
            '/employee/test',
            'عرض'
        );

        $notification = \DB::table('notifications')->first();
        $data = json_decode($notification->data, true);
        expect($data['title'])->toBe('Assigned Item');
        expect($data['format'])->toBe('filament');
        expect($notification->notifiable_id)->toBe($this->employee->id);
    });
});

describe('ComplaintObserver notifications', function () {
    it('sends Filament notification to admin when complaint is created', function () {
        $this->assertDatabaseCount('notifications', 0);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $this->complaintType->id,
            'description' => 'Test complaint description text.',
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('notifications', 1);
        $notification = \DB::table('notifications')->first();
        $data = json_decode($notification->data, true);
        expect($data['format'])->toBe('filament');
        expect($data['title'])->toContain('شكوى جديدة');
        expect($notification->notifiable_id)->toBe($this->admin->id);

        $this->assertDatabaseHas('custom_notifications', [
            'user_id' => $this->admin->id,
        ]);
    });

    it('sends notification to assigned employee when complaint is created', function () {
        Complaint::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $this->complaintType->id,
            'description' => 'Test complaint with assignee.',
            'status' => 'pending',
            'assigned_to' => $this->employee->id,
        ]);

        $employeeNotifications = \DB::table('notifications')
            ->where('notifiable_id', $this->employee->id)
            ->count();
        expect($employeeNotifications)->toBe(1);

        $this->assertDatabaseHas('custom_notifications', [
            'user_id' => $this->employee->id,
        ]);
    });

    it('sends notification to citizen when complaint status is updated', function () {
    Http::fake([
        '*' => Http::response([
            'choices' => [['message' => ['content' => '{"summary":"S","priority":"medium"}']]],
        ], 200),
    ]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $this->complaintType->id,
            'description' => 'Test complaint for status update.',
            'status' => 'pending',
        ]);

        $initialNotifications = \DB::table('notifications')->count();

        $complaint->status = 'completed';
        $complaint->save();

        $totalNotifications = \DB::table('notifications')->count();
        expect($totalNotifications)->toBeGreaterThan($initialNotifications);

        $citizenNotification = \DB::table('notifications')
            ->where('notifiable_id', $this->citizen->id)
            ->first();
        expect($citizenNotification)->not->toBeNull();

        $data = json_decode($citizenNotification->data, true);
        expect($data['format'])->toBe('filament');
    });
});

describe('InquiryObserver notifications', function () {
    it('sends Filament notification to admin when inquiry is created', function () {
        $this->assertDatabaseCount('notifications', 0);

        Inquiry::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $this->inquiryType->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('notifications', 1);
        $notification = \DB::table('notifications')->first();
        $data = json_decode($notification->data, true);
        expect($data['format'])->toBe('filament');
        expect($data['title'])->toContain('استعلام جديد');
        expect($notification->notifiable_id)->toBe($this->admin->id);

        $this->assertDatabaseHas('custom_notifications', [
            'user_id' => $this->admin->id,
        ]);
    });

    it('sends notification to assigned employee when inquiry is created', function () {
        Inquiry::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $this->inquiryType->id,
            'status' => 'pending',
            'assigned_to' => $this->employee->id,
        ]);

        $employeeNotifications = \DB::table('notifications')
            ->where('notifiable_id', $this->employee->id)
            ->count();
        expect($employeeNotifications)->toBe(1);

        $this->assertDatabaseHas('custom_notifications', [
            'user_id' => $this->employee->id,
        ]);
    });

    it('sends notification to citizen when inquiry status is updated', function () {
        Inquiry::create([
            'citizen_id' => $this->citizen->id,
            'type_id' => $this->inquiryType->id,
            'status' => 'pending',
        ]);

        $initialNotifications = \DB::table('notifications')->count();

        $inquiry = Inquiry::first();
        $inquiry->status = 'completed';
        $inquiry->save();

        $totalNotifications = \DB::table('notifications')->count();
        expect($totalNotifications)->toBeGreaterThan($initialNotifications);

        $citizenNotification = \DB::table('notifications')
            ->where('notifiable_id', $this->citizen->id)
            ->first();
        expect($citizenNotification)->not->toBeNull();
    });
});