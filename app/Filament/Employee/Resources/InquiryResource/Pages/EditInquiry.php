<?php

namespace App\Filament\Employee\Resources\InquiryResource\Pages;

use App\Filament\Employee\Resources\InquiryResource;
use Filament\Resources\Pages\EditRecord;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
