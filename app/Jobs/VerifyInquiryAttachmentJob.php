<?php

namespace App\Jobs;

use App\Models\InquiryAttachment;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerifyInquiryAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 320;

    public int $tries = 1;

    public function __construct(
        public int $attachmentId,
        public string $storagePath
    ) {}

    public function handle(): void
    {
        $attachment = InquiryAttachment::find($this->attachmentId);

        if (!$attachment) {
            Log::warning('VerifyInquiryAttachmentJob: Attachment not found', [
                'attachment_id' => $this->attachmentId,
            ]);
            return;
        }

        $absolutePath = Storage::disk('public')->path($this->storagePath);

        if (!file_exists($absolutePath)) {
            Log::warning('VerifyInquiryAttachmentJob: File not found on disk', [
                'attachment_id' => $this->attachmentId,
                'path' => $this->storagePath,
            ]);
            return;
        }

        $base64Image = base64_encode(file_get_contents($absolutePath));
        $mimeType = $attachment->file_type;

        $verification = AiService::verifyAttachment($base64Image, $mimeType);

        if ($verification) {
            $attachment->update([
                'is_ai_verified' => $verification['is_valid'],
                'ai_ocr_text' => $verification['extracted_text'] ?? null,
            ]);
        } else {
            Log::warning('VerifyInquiryAttachmentJob: AI verification returned null', [
                'attachment_id' => $this->attachmentId,
            ]);
        }
    }
}
