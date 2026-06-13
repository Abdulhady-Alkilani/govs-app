<?php

namespace App\Observers;

use App\Models\Complaint;
use App\Models\User;
use App\Services\NotificationService;

class ComplaintObserver
{
    public function created(Complaint $complaint): void
    {
        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();

        NotificationService::sendToUsers(
            $admins,
            __('شكوى جديدة #') . $complaint->id,
            __('تم تقديم شكوى جديدة من المواطن :name', [
                'name' => $complaint->citizen?->name ?? 'مواطن',
            ]),
            'heroicon-o-exclamation-triangle',
            'warning',
            '/admin/complaints/' . $complaint->id . '/edit',
        );

        if ($complaint->assigned_to) {
            $employee = User::find($complaint->assigned_to);
            if ($employee) {
                NotificationService::sendToUser(
                    $employee,
                    __('شكوى جديدة مسندة إليك #') . $complaint->id,
                    __('تم إسناد شكوى جديدة إليك'),
                    'heroicon-o-document-text',
                    'info',
                    '/employee/complaints/' . $complaint->id . '/edit',
                );
            }
        }
    }

    public function updated(Complaint $complaint): void
    {
        if ($complaint->isDirty('status')) {
            $statusMap = [
                'pending' => __('Pending'),
                'processing' => __('Processing'),
                'completed' => __('Completed'),
                'rejected' => __('Rejected'),
            ];

            $statusText = $statusMap[$complaint->status] ?? $complaint->status;

            $citizen = User::find($complaint->citizen_id);
            if ($citizen) {
                NotificationService::sendToUser(
                    $citizen,
                    __('Complaint status updated'),
                    __('The status of your complaint #:id has been updated to: :status_text', [
                        'id' => $complaint->id,
                        'status_text' => $statusText,
                    ]),
                    'heroicon-o-arrow-path',
                    'info',
                    route('complaints.show', $complaint->id),
                );
            }
        }
    }
}