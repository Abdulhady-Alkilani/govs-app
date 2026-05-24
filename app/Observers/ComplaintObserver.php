<?php

namespace App\Observers;

use App\Models\Complaint;
use App\Models\Notification;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;

class ComplaintObserver
{
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

            Notification::create([
                'user_id' => $complaint->citizen_id,
                'title' => __('Complaint status updated'),
                'message' => __('The status of your complaint #:status has been updated to: :status_text', [
                    'status' => $complaint->id,
                    'status_text' => $statusText,
                ]),
                'action_url' => route('complaints.show', $complaint->id),
                'is_read' => false,
            ]);

            $citizen = User::find($complaint->citizen_id);
            if ($citizen) {
                FilamentNotification::make()
                    ->title(__('Complaint status updated'))
                    ->body(__('The status of your complaint #:status has been updated to: :status_text', [
                        'status' => $complaint->id,
                        'status_text' => $statusText,
                    ]))
                    ->sendToDatabase($citizen);
            }
        }
    }
}
