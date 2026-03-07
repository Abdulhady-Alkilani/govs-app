<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 
        'model_id', 'old_value', 'new_value'
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'json',
            'new_value' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // علاقة بوليمورفيك (متعددة الأشكال) لربط السجل بأي جدول (شكوى، فاتورة، استعلام)
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}