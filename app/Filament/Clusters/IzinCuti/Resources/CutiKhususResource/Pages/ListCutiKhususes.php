<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource;
use App\Models\CutiKhusus;

class ListCutiKhususes extends ListRecords
{
    protected static string $resource = CutiKhususResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Cuti Khusus'),
        ];
    }

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            null => Tab::make('All')
                ->badge(fn() => CutiKhusus::whereHas('user', fn($query) => $query->where('user_id', $userId))->count()),

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
                ->badge(fn() => CutiKhusus::whereHas(
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
                ->badge(fn() => CutiKhusus::whereHas(
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
                ->badge(fn() => CutiKhusus::whereHas(
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
}
