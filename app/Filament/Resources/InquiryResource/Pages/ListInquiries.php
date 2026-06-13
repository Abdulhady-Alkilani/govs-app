<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use App\Services\AiService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInquiries extends ListRecords
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
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
