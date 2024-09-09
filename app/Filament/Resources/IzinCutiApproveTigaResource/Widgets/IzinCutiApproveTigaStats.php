<?php

namespace App\Filament\Resources\IzinCutiApproveTigaResource\Widgets;

use App\Filament\Resources\IzinCutiApproveTigaResource\Pages\ListIzinCutiApproveTigas;
use App\Models\IzinCutiApproveTiga;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class IzinCutiApproveTigaStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Proccessing', number_format(IzinCutiApproveTiga::where('status', 0)->count(), 0, ',', '.')),
            Stat::make('Total Approved', number_format(IzinCutiApproveTiga::where('status', 1)->count(), 0, ',', '.')),
            Stat::make('Total Rejected', number_format(IzinCutiApproveTiga::where('status', 2)->count(), 0, ',', '.')),
        ];
    }
}
