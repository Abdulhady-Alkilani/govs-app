<?php

namespace App\Observers;

use App\Models\Inquiry;
use App\Models\Notification;

class InquiryObserver
{
    public function updated(Inquiry $inquiry): void
    {
        if ($inquiry->isDirty('status')) {
            $statusMap = [
                'pending' => 'قيد الانتظار',
                'processing' => 'قيد المعالجة',
                'completed' => 'مكتمل',
                'rejected' => 'مرفوض',
            ];

            $statusAr = $statusMap[$inquiry->status] ?? $inquiry->status;

            Notification::create([
                'user_id' => $inquiry->citizen_id,
                'title' => 'تحديث حالة الاستعلام',
                'message' => "تم تحديث حالة استعلامك رقم #{$inquiry->id} إلى: ".$statusAr,
                'is_read' => false,
            ]);
        }
    }
}
