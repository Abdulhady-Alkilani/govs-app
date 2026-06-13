<?php

namespace App\Filament\Employee\Resources\ComplaintResource\Pages;

use App\Filament\Employee\Resources\ComplaintResource;
use App\Services\AiService;
use Filament\Resources\Pages\ListRecords;

class ListComplaints extends ListRecords
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function generateAiReply(string $quickNote): array
    {
        if (strlen($quickNote) < 5) {
            return ['success' => false, 'message' => 'الملاحظة قصيرة جداً'];
        }

        $reply = AiService::generateOfficialReply($quickNote);

        if (!$reply) {
            return ['success' => false, 'message' => 'فشل توليد الرد. يرجى المحاولة مرة أخرى.'];
        }

        return ['success' => true, 'reply' => $reply];
    }
}
