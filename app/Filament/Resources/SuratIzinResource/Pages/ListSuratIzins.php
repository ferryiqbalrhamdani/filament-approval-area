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

            'proccessing' => Tab::make('Total Proccessing')
                ->badge(
                    fn() => SuratIzin::where('user_id', $userId)
                        ->where(function ($query) {
                            $query->whereHas('suratIzinApprove', fn($query) => $query->where('status', 0))
                                ->orWhereHas('suratIzinApproveDua', fn($query) => $query->where('status', 0))
                                ->orWhereHas('suratIzinApproveTiga', fn($query) => $query->where('status', 0));
                        })
                        ->count()
                )
                ->modifyQueryUsing(
                    fn(Builder $query) => $query
                        ->where('user_id', $userId)
                        ->where(function ($query) {
                            $query->whereHas('suratIzinApprove', fn($query) => $query->where('status', 0))
                                ->orWhereHas('suratIzinApproveDua', fn($query) => $query->where('status', 0))
                                ->orWhereHas('suratIzinApproveTiga', fn($query) => $query->where('status', 0));
                        })
                ),
            'approved' => Tab::make('Total Approved')
                ->badge(
                    fn() => SuratIzin::where('user_id', $userId)
                        ->where(function ($query) {
                            $query->whereHas('suratIzinApprove', fn($query) => $query->where('status', 1))
                                ->orWhereHas('suratIzinApproveDua', fn($query) => $query->where('status', 1))
                                ->orWhereHas('suratIzinApproveTiga', fn($query) => $query->where('status', 1));
                        })
                        ->count()
                )
                ->modifyQueryUsing(
                    fn(Builder $query) => $query
                        ->where('user_id', $userId)
                        ->where(function ($query) {
                            $query->whereHas('suratIzinApprove', fn($query) => $query->where('status', 1))
                                ->orWhereHas('suratIzinApproveDua', fn($query) => $query->where('status', 1))
                                ->orWhereHas('suratIzinApproveTiga', fn($query) => $query->where('status', 1));
                        })
                ),
            'rejected' => Tab::make('Total Rejected')
                ->badge(
                    fn() => SuratIzin::where('user_id', $userId)
                        ->where(function ($query) {
                            $query->whereHas('suratIzinApprove', fn($query) => $query->where('status', 2))
                                ->orWhereHas('suratIzinApproveDua', fn($query) => $query->where('status', 2))
                                ->orWhereHas('suratIzinApproveTiga', fn($query) => $query->where('status', 2));
                        })
                        ->count()
                )
                ->modifyQueryUsing(
                    fn(Builder $query) => $query
                        ->where('user_id', $userId)
                        ->where(function ($query) {
                            $query->whereHas('suratIzinApprove', fn($query) => $query->where('status', 2))
                                ->orWhereHas('suratIzinApproveDua', fn($query) => $query->where('status', 2))
                                ->orWhereHas('suratIzinApproveTiga', fn($query) => $query->where('status', 2));
                        })
                ),
        ];
    }


    protected function makeTable(): Table
    {
        return parent::makeTable()->recordUrl(null);
    }
}
