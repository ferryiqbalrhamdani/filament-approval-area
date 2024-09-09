<?php

namespace App\Filament\Resources\IzinCutiApproveTigaResource\Pages;

use App\Filament\Resources\IzinCutiApproveTigaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinCutiApproveTiga extends EditRecord
{
    protected static string $resource = IzinCutiApproveTigaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
