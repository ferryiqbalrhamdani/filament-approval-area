<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\Pages;

use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCutiKhusus extends EditRecord
{
    protected static string $resource = CutiKhususResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
