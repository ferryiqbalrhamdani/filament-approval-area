<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CutiPribadiStats extends BaseWidget
{

    protected function getStats(): array
    {
        return [
            Stat::make('Sisa Cuti', Auth::user()->cuti),
        ];
    }
}
