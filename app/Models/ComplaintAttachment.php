<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintAttachment extends Model
{
    protected $fillable = ['complaint_id', 'file_path', 'file_type', 'is_ai_verified', 'ai_ocr_text'];

    protected $casts = [
        'is_ai_verified' => 'boolean',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}