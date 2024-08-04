<?php

namespace App\Filament\Resources\SuratIzinApproveResource\Pages;

use App\Filament\Resources\SuratIzinApproveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratIzinApproves extends ListRecords
{
    protected static string $resource = SuratIzinApproveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
