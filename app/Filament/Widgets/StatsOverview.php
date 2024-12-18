<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        $user = Auth::user();

        // Calculate the counts per month for suratIzin, cuti, and lembur
        $suratIzinCounts = $this->getMonthlyCounts($user->suratIzin());
        $cutiCounts = $this->getMonthlyCounts($user->cuti());
        $lemburCounts = $this->getMonthlyCounts($user->lembur());

        return [
            Stat::make('Total Izin', $user->suratIzin()->count())
                ->color($user->suratIzin()->count() > 0 ? 'success' : 'gray')
                ->chart($suratIzinCounts), // Pass the calculated monthly counts for the chart
            Stat::make('Total Cuti', $user->cuti()->count())
                ->color($user->cuti()->count() > 0 ? 'success' : 'gray')
                ->chart($cutiCounts), // Pass the calculated monthly counts for the chart
            Stat::make('Total Lembur', $user->lembur()->count())
                ->color($user->lembur()->count() > 0 ? 'success' : 'gray')
                ->chart($lemburCounts), // Pass the calculated monthly counts for the chart
        ];
    }

    // Helper function to calculate counts per month for the last 12 months
    protected function getMonthlyCounts($query)
    {
        // Get the last 12 months and initialize an array to store the monthly counts
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Count the records grouped by month using PostgreSQL's TO_CHAR function
        $counts = $query->selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, COUNT(*) as count')
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Return the counts for each month, or 0 if no records exist for a given month
        return $months->map(function ($month) use ($counts) {
            return $counts[$month] ?? 0;
        })->toArray();
    }
}
