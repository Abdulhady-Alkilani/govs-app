<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryAttachment extends Model
{
    protected $fillable = ['inquiry_id', 'file_path', 'file_name', 'file_type', 'is_ai_verified', 'ai_ocr_text'];

    protected $casts = [
        'is_ai_verified' => 'boolean',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }
}
