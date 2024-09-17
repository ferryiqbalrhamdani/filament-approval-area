<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Filament\Actions;
use App\Models\CutiPribadi;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;

class ListCutiPribadis extends ListRecords
{
    protected static string $resource = CutiPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Cuti Pribadi')
                ->disabled(fn() => Auth::user()->cuti <= 0),
        ];
    }

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            null => Tab::make('All')
                ->badge(fn() => CutiPribadi::whereHas('user', fn($query) => $query->where('user_id', $userId))->count()),

            'proccessing' => Tab::make('Total Proccessing')
                ->query(fn($query) => $query->whereHas(
                    'izinCutiApprove',
                    fn($q) =>
                    $q->where('status', 0)
                        ->orWhereHas(
                            'izinCutiApproveDua',
                            fn($q2) =>
                            $q2->where('status', 0)
                                ->orWhereHas(
                                    'izinCutiApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 0)
                                )
                        )
                ))
                ->badge(fn() => CutiPribadi::whereHas(
                    'izinCutiApprove',
                    fn($q) =>
                    $q->where('status', 0)
                        ->orWhereHas(
                            'izinCutiApproveDua',
                            fn($q2) =>
                            $q2->where('status', 0)
                                ->orWhereHas(
                                    'izinCutiApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 0)
                                )
                        )
                )->count()),
            'approved' => Tab::make('Total Approved')
                ->query(fn($query) => $query->whereHas(
                    'izinCutiApprove',
                    fn($q) =>
                    $q->where('status', 1)
                        ->orWhereHas(
                            'izinCutiApproveDua',
                            fn($q2) =>
                            $q2->where('status', 1)
                                ->orWhereHas(
                                    'izinCutiApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 1)
                                )
                        )
                ))
                ->badge(fn() => CutiPribadi::whereHas(
                    'izinCutiApprove',
                    fn($q) =>
                    $q->where('status', 1)
                        ->orWhereHas(
                            'izinCutiApproveDua',
                            fn($q2) =>
                            $q2->where('status', 1)
                                ->orWhereHas(
                                    'izinCutiApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 1)
                                )
                        )
                )->count()),
            'rejected' => Tab::make('Total Rejected')
                ->query(fn($query) => $query->whereHas(
                    'izinCutiApprove',
                    fn($q) =>
                    $q->where('status', 2)
                        ->orWhereHas(
                            'izinCutiApproveDua',
                            fn($q2) =>
                            $q2->where('status', 2)
                                ->orWhereHas(
                                    'izinCutiApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 2)
                                )
                        )
                ))
                ->badge(fn() => CutiPribadi::whereHas(
                    'izinCutiApprove',
                    fn($q) =>
                    $q->where('status', 2)
                        ->orWhereHas(
                            'izinCutiApproveDua',
                            fn($q2) =>
                            $q2->where('status', 2)
                                ->orWhereHas(
                                    'izinCutiApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 2)
                                )
                        )
                )->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return CutiPribadiResource::getWidgets();
    }
}
