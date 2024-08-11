<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use App\Filament\Resources\IzinLemburResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIzinLemburs extends ListRecords
{
    protected static string $resource = IzinLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Izin Lembur'),
        ];
    }
}
