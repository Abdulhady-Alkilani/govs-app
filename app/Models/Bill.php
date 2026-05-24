<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bill extends Model
{
    protected $fillable = [
        'citizen_id', 'bill_type', 'amount', 'paid_amount', 'payment_receipt_path', 'payment_details',
        'status', 'due_date', 'paid_at', 'transaction_id'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    protected static function booted()
    {
        static::created(function ($bill) {
            $billTypeName = match ($bill->bill_type) {
                'water' => 'فاتورة مياه',
                'electricity' => 'فاتورة كهرباء',
                'telecom' => 'فاتورة اتصالات',
                'property_tax' => 'ضريبة عقارية',
                'traffic_fine' => 'مخالفة سير',
                'late_fine' => 'غرامة تأخير',
                default => $bill->bill_type,
            };

            \App\Models\Notification::create([
                'user_id' => $bill->citizen_id,
                'title' => 'لديك فاتورة / غرامة جديدة',
                'message' => 'تم إصدار مطالبة مالية جديدة لحسابك بنوع: ' . $billTypeName,
                'action_url' => route('bills.pay', $bill->id),
            ]);
        });
    }
}