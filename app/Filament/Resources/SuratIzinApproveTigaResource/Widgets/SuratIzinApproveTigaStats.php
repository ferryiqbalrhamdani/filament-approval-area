<?php

namespace App\Filament\Resources\SuratIzinApproveTigaResource\Widgets;

use App\Models\SuratIzinApproveTiga;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuratIzinApproveTigaStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Proccessing', number_format(SuratIzinApproveTiga::where('status', 0)->count(), 0, ',', '.')),
            Stat::make('Total Approved', number_format(SuratIzinApproveTiga::where('status', 1)->count(), 0, ',', '.')),
            Stat::make('Total Rejected', number_format(SuratIzinApproveTiga::where('status', 2)->count(), 0, ',', '.')),
        ];
    }
}
