<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use Filament\Actions;
use App\Models\IzinLemburApprove;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\IzinLemburResource;

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

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            null => Tab::make('All')
                ->badge(fn() => IzinLemburApprove::whereHas('IzinLembur', fn($query) => $query->where('user_id', $userId))->count()),

            'proccessing' => Tab::make('Total Processing')
                ->query(fn($query) => $query->whereHas(
                    'IzinLemburApprove',
                    fn($q) =>
                    $q->where('status', 0)
                        ->orWhereHas(
                            'IzinLemburApproveDua',
                            fn($q2) =>
                            $q2->where('status', 0)
                                ->orWhereHas(
                                    'IzinLemburApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 0)
                                )
                        )
                ))
                ->badge(fn() => IzinLemburApprove::whereHas('IzinLembur', fn($query) => $query->where('user_id', $userId))
                    ->where(function ($q) {
                        $q->where('status', 0)
                            ->orWhereHas(
                                'IzinLemburApproveDua',
                                fn($q2) =>
                                $q2->where('status', 0)
                                    ->orWhereHas(
                                        'IzinLemburApproveTiga',
                                        fn($q3) =>
                                        $q3->where('status', 0)
                                    )
                            );
                    })
                    ->count()),

            'approved' => Tab::make('Total Approved')
                ->query(fn($query) => $query->whereHas(
                    'IzinLemburApprove',
                    fn($q) =>
                    $q->where('status', 1)
                        ->orWhereHas(
                            'IzinLemburApproveDua',
                            fn($q2) =>
                            $q2->where('status', 1)
                                ->orWhereHas(
                                    'IzinLemburApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 1)
                                )
                        )
                ))
                ->badge(fn() => IzinLemburApprove::whereHas('IzinLembur', fn($query) => $query->where('user_id', $userId))
                    ->where(function ($q) {
                        $q->where('status', 1)
                            ->orWhereHas(
                                'IzinLemburApproveDua',
                                fn($q2) =>
                                $q2->where('status', 1)
                                    ->orWhereHas(
                                        'IzinLemburApproveTiga',
                                        fn($q3) =>
                                        $q3->where('status', 1)
                                    )
                            );
                    })
                    ->count()),

            'rejected' => Tab::make('Total Rejected')
                ->query(fn($query) => $query->whereHas(
                    'IzinLemburApprove',
                    fn($q) =>
                    $q->where('status', 2)
                        ->orWhereHas(
                            'IzinLemburApproveDua',
                            fn($q2) =>
                            $q2->where('status', 2)
                                ->orWhereHas(
                                    'IzinLemburApproveTiga',
                                    fn($q3) =>
                                    $q3->where('status', 2)
                                )
                        )
                ))
                ->badge(fn() => IzinLemburApprove::whereHas('IzinLembur', fn($query) => $query->where('user_id', $userId))
                    ->where(function ($q) {
                        $q->where('status', 2)
                            ->orWhereHas(
                                'IzinLemburApproveDua',
                                fn($q2) =>
                                $q2->where('status', 2)
                                    ->orWhereHas(
                                        'IzinLemburApproveTiga',
                                        fn($q3) =>
                                        $q3->where('status', 2)
                                    )
                            );
                    })
                    ->count()),
        ];
    }
}
