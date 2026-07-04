<?php

namespace App\Observers;

use App\Models\Inquiry;
use App\Models\User;
use App\Services\NotificationService;

class InquiryObserver
{
    public function created(Inquiry $inquiry): void
    {
        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();

        NotificationService::sendToUsers(
            $admins,
            __('استعلام جديد #') . $inquiry->id,
            __('تم تقديم استعلام جديد من المواطن :name', [
                'name' => $inquiry->citizen?->name ?? 'مواطن',
            ]),
            'heroicon-o-magnifying-glass',
            'info',
            '/admin/inquiries/' . $inquiry->id . '/edit',
        );

        if ($inquiry->assigned_to) {
            $employee = User::find($inquiry->assigned_to);
            if ($employee) {
                NotificationService::sendToUser(
                    $employee,
                    __('استعلام جديد مسند إليك #') . $inquiry->id,
                    __('تم إسناد استعلام جديد إليك'),
                    'heroicon-o-document-text',
                    'info',
                    '/employee/inquiries/' . $inquiry->id . '/edit',
                );
            }
        }
    }

    public function updated(Inquiry $inquiry): void
    {
        if ($inquiry->isDirty('status')) {
            $statusMap = [
                'pending' => __('Pending'),
                'processing' => __('Processing'),
                'completed' => __('Completed'),
                'rejected' => __('Rejected'),
            ];

            $statusText = $statusMap[$inquiry->status] ?? $inquiry->status;

            $citizen = User::find($inquiry->citizen_id);
            if ($citizen) {
                NotificationService::sendToUser(
                    $citizen,
                    __('Inquiry status updated'),
                    __('The status of your inquiry #:id has been updated to: :status_text', [
                        'id' => $inquiry->id,
                        'status_text' => $statusText,
                    ]),
                    'heroicon-o-arrow-path',
                    'success',
                    route('inquiries.show', $inquiry->id, false),
                );
            }
        }
    }
}