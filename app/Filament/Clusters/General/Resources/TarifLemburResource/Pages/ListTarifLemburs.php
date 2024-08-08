<?php

namespace App\Filament\Clusters\General\Resources\TarifLemburResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\General\Resources\TarifLemburResource;

class ListTarifLemburs extends ListRecords
{
    protected static string $resource = TarifLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            Tab::make('All')
                ->badge(fn() => $this->getAllRecordsCount()),

            Tab::make('Weekday')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status_hari', 'Weekday'))
                ->badge(fn() => $this->getFilteredRecordsCount('Weekday')),

            Tab::make('Weekend')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status_hari', 'Weekend'))
                ->badge(fn() => $this->getFilteredRecordsCount('Weekend')),
        ];
    }

    protected function getAllRecordsCount(): int
    {
        return static::getResource()::getEloquentQuery()->count();
    }

    protected function getFilteredRecordsCount(string $status): int
    {
        return static::getResource()::getEloquentQuery()->where('status_hari', $status)->count();
    }
}
