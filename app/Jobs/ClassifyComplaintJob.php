<?php

namespace App\Jobs;

use App\Models\Complaint;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClassifyComplaintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 200;

    public int $tries = 1;

    public function __construct(
        public int $complaintId
    ) {}

    public function handle(): void
    {
        $complaint = Complaint::with('type')->find($this->complaintId);

        if (!$complaint) {
            Log::warning('ClassifyComplaintJob: Complaint not found', [
                'complaint_id' => $this->complaintId,
            ]);
            return;
        }

        $typeName = $complaint->type?->name;
        $classification = AiService::classifyComplaint($complaint->description, $typeName);

        if ($classification) {
            $complaint->update([
                'ai_summary' => $classification['summary'],
                'ai_priority' => $classification['priority'],
            ]);
        } else {
            Log::warning('ClassifyComplaintJob: AI classification returned null', [
                'complaint_id' => $this->complaintId,
            ]);
        }
    }
}
