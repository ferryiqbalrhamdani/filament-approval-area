<?php

namespace App\Filament\Resources\IzinLemburApproveTigaResource\Pages;

use App\Filament\Resources\IzinLemburApproveTigaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinLemburApproveTiga extends EditRecord
{
    protected static string $resource = IzinLemburApproveTigaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
