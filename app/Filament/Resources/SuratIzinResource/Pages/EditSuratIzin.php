<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use App\Filament\Resources\SuratIzinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratIzin extends EditRecord
{
    protected static string $resource = SuratIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
