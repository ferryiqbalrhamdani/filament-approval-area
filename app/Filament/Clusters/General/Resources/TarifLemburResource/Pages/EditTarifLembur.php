<?php

namespace App\Filament\Clusters\General\Resources\TarifLemburResource\Pages;

use App\Filament\Clusters\General\Resources\TarifLemburResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTarifLembur extends EditRecord
{
    protected static string $resource = TarifLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
