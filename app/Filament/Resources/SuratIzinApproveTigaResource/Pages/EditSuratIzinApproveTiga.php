<?php

namespace App\Filament\Resources\SuratIzinApproveTigaResource\Pages;

use App\Filament\Resources\SuratIzinApproveTigaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratIzinApproveTiga extends EditRecord
{
    protected static string $resource = SuratIzinApproveTigaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
