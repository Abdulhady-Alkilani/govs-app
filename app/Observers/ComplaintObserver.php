<?php

namespace App\Observers;

use App\Models\Complaint;
use App\Models\Notification;

class ComplaintObserver
{
    public function updated(Complaint $complaint): void
    {
        if ($complaint->isDirty('status')) {
            $statusMap = [
                'pending' => 'قيد الانتظار',
                'processing' => 'قيد المعالجة',
                'completed' => 'مكتمل',
                'rejected' => 'مرفوض',
            ];

            $statusAr = $statusMap[$complaint->status] ?? $complaint->status;

            Notification::create([
                'user_id' => $complaint->citizen_id,
                'title' => 'تحديث حالة الشكوى',
                'message' => "تم تحديث حالة شكواك رقم #{$complaint->id} إلى: ".$statusAr,
                'is_read' => false,
            ]);
        }
    }
}
