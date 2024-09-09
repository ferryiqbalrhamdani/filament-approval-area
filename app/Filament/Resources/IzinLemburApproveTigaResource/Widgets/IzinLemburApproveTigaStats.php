<?php

namespace App\Filament\Resources\IzinLemburApproveTigaResource\Widgets;

use App\Models\IzinLemburApproveTiga;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class IzinLemburApproveTigaStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Proccessing', number_format(IzinLemburApproveTiga::where('status', 0)->count(), 0, ',', '.')),
            Stat::make('Total Approved', number_format(IzinLemburApproveTiga::where('status', 1)->count(), 0, ',', '.')),
            Stat::make('Total Rejected', number_format(IzinLemburApproveTiga::where('status', 2)->count(), 0, ',', '.')),
        ];
    }
}
