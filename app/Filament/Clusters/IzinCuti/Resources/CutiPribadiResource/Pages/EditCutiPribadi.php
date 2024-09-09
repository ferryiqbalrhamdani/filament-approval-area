<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCutiPribadi extends EditRecord
{
    protected static string $resource = CutiPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
