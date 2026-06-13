<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use App\Services\AiService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListComplaints extends ListRecords
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Livewire method to generate AI reply — called from JavaScript via $wire.call()
     */
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
