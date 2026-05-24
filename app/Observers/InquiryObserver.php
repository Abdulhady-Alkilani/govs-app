<?php

namespace App\Observers;

use App\Models\Inquiry;
use App\Models\Notification;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;

class InquiryObserver
{
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

            Notification::create([
                'user_id' => $inquiry->citizen_id,
                'title' => __('Inquiry status updated'),
                'message' => __('The status of your inquiry #:status has been updated to: :status_text', [
                    'status' => $inquiry->id,
                    'status_text' => $statusText,
                ]),
                'action_url' => route('inquiries.show', $inquiry->id),
                'is_read' => false,
            ]);

            $citizen = User::find($inquiry->citizen_id);
            if ($citizen) {
                FilamentNotification::make()
                    ->title(__('Inquiry status updated'))
                    ->body(__('The status of your inquiry #:status has been updated to: :status_text', [
                        'status' => $inquiry->id,
                        'status_text' => $statusText,
                    ]))
                    ->sendToDatabase($citizen);
            }
        }
    }
}
