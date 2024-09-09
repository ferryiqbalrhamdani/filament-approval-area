<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use Filament\Actions;
use App\Models\SuratIzin;
use Filament\Tables\Table;
use App\Models\SuratIzinApprove;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SuratIzinResource;



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
                ->badge(fn() => SuratIzin::where('user_id', $userId)->count()),

            'processing' => Tab::make('Total Processing')
                ->query(fn($query) => $query->whereHas(
                    'suratIzinApprove',
                    fn($q) =>
                    $q->where('status', 0)

                )
                    ->orWhereHas(
                        'suratIzinApproveDua',
                        fn($q2) =>
                        $q2->where('status', 0)

                    )
                    ->orWhereHas(
                        'suratIzinApproveTiga',
                        fn($q3) =>
                        $q3->where('status', 0)
                    ))
                ->badge(fn() => SuratIzin::where('user_id', $userId)
                    ->whereHas('suratIzinApprove', fn($q1) => $q1->where('status', 0))
                    ->orWhereHas('suratIzinApproveDua', fn($q2) => $q2->where('status', 0))
                    ->orWhereHas('suratIzinApproveTiga', fn($q3) => $q3->where('status', 0))
                    ->count()),


            'approved' => Tab::make('Total Approved')
                ->query(fn($query) => $query->whereHas(
                    'suratIzinApprove',
                    fn($q) =>
                    $q->where('status', 1)

                )
                    ->orWhereHas(
                        'suratIzinApproveDua',
                        fn($q2) =>
                        $q2->where('status', 1)

                    )
                    ->orWhereHas(
                        'suratIzinApproveTiga',
                        fn($q3) =>
                        $q3->where('status', 1)
                    ))
                ->badge(fn() => SuratIzin::where('user_id', $userId)
                    ->whereHas('suratIzinApprove', fn($q1) => $q1->where('status', 1))
                    ->orWhereHas('suratIzinApproveDua', fn($q2) => $q2->where('status', 1))
                    ->orWhereHas('suratIzinApproveTiga', fn($q3) => $q3->where('status', 1))
                    ->count()),

            'rejected' => Tab::make('Total Rejected')
                ->query(fn($query) => $query->whereHas(
                    'suratIzinApprove',
                    fn($q) =>
                    $q->where('status', 2)

                )
                    ->orWhereHas(
                        'suratIzinApproveDua',
                        fn($q2) =>
                        $q2->where('status', 2)

                    )
                    ->orWhereHas(
                        'suratIzinApproveTiga',
                        fn($q3) =>
                        $q3->where('status', 2)
                    ))
                ->badge(fn() => SuratIzin::where('user_id', $userId)
                    ->whereHas('suratIzinApprove', fn($q1) => $q1->where('status', 2))
                    ->orWhereHas('suratIzinApproveDua', fn($q2) => $q2->where('status', 2))
                    ->orWhereHas('suratIzinApproveTiga', fn($q3) => $q3->where('status', 2))
                    ->count()),
        ];
    }

    protected function makeTable(): Table
    {
        return parent::makeTable()->recordUrl(null);
    }
}
