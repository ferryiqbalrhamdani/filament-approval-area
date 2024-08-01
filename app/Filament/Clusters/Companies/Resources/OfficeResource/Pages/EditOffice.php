<?php

namespace App\Filament\Clusters\Companies\Resources\OfficeResource\Pages;

use App\Filament\Clusters\Companies\Resources\OfficeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOffice extends EditRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
