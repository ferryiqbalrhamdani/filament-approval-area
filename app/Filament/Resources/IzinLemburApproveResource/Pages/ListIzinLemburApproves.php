<?php

namespace App\Filament\Resources\IzinLemburApproveResource\Pages;

use App\Filament\Resources\IzinLemburApproveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIzinLemburApproves extends ListRecords
{
    protected static string $resource = IzinLemburApproveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
