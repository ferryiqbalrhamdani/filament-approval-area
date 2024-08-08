<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use App\Filament\Resources\SuratIzinResource;
use App\Models\SuratIzinApprove;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;



class ListSuratIzins extends ListRecords
{
    protected static string $resource = SuratIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Surat Izin'),
        ];
    }

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            null => Tab::make('All')
                ->badge(fn () => SuratIzinApprove::whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))->count()),

            'processing' => Tab::make('Total Processing')
                ->query(fn ($query) => $query->whereHas(
                    'suratIzinApprove',
                    fn ($q) =>
                    $q->where('status', 0)
                        ->orWhereHas(
                            'suratIzinApproveDua',
                            fn ($q2) =>
                            $q2->where('status', 0)
                                ->orWhereHas(
                                    'suratIzinApproveTiga',
                                    fn ($q3) =>
                                    $q3->where('status', 0)
                                )
                        )
                ))
                ->badge(fn () => SuratIzinApprove::whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))
                    ->where(function ($q) {
                        $q->where('status', 0)
                            ->orWhereHas(
                                'suratIzinApproveDua',
                                fn ($q2) =>
                                $q2->where('status', 0)
                                    ->orWhereHas(
                                        'suratIzinApproveTiga',
                                        fn ($q3) =>
                                        $q3->where('status', 0)
                                    )
                            );
                    })
                    ->count()),

            'approved' => Tab::make('Total Approved')
                ->query(fn ($query) => $query->whereHas(
                    'suratIzinApprove',
                    fn ($q) =>
                    $q->where('status', 1)
                        ->orWhereHas(
                            'suratIzinApproveDua',
                            fn ($q2) =>
                            $q2->where('status', 1)
                                ->orWhereHas(
                                    'suratIzinApproveTiga',
                                    fn ($q3) =>
                                    $q3->where('status', 1)
                                )
                        )
                ))
                ->badge(fn () => SuratIzinApprove::whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))
                    ->where(function ($q) {
                        $q->where('status', 1)
                            ->orWhereHas(
                                'suratIzinApproveDua',
                                fn ($q2) =>
                                $q2->where('status', 1)
                                    ->orWhereHas(
                                        'suratIzinApproveTiga',
                                        fn ($q3) =>
                                        $q3->where('status', 1)
                                    )
                            );
                    })
                    ->count()),

            'rejected' => Tab::make('Total Rejected')
                ->query(fn ($query) => $query->whereHas(
                    'suratIzinApprove',
                    fn ($q) =>
                    $q->where('status', 2)
                        ->orWhereHas(
                            'suratIzinApproveDua',
                            fn ($q2) =>
                            $q2->where('status', 2)
                                ->orWhereHas(
                                    'suratIzinApproveTiga',
                                    fn ($q3) =>
                                    $q3->where('status', 2)
                                )
                        )
                ))
                ->badge(fn () => SuratIzinApprove::whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))
                    ->where(function ($q) {
                        $q->where('status', 2)
                            ->orWhereHas(
                                'suratIzinApproveDua',
                                fn ($q2) =>
                                $q2->where('status', 2)
                                    ->orWhereHas(
                                        'suratIzinApproveTiga',
                                        fn ($q3) =>
                                        $q3->where('status', 2)
                                    )
                            );
                    })
                    ->count()),
        ];
    }

    // protected function makeTable(): Table
    // {
    //     return parent::makeTable()->recordUrl(null);
    // }
}
