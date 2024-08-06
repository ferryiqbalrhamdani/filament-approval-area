<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use App\Filament\Resources\SuratIzinResource;
use App\Models\SuratIzinApprove;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSuratIzins extends ListRecords
{
    protected static string $resource = SuratIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            null => Tab::make('All')
                ->badge(fn () => SuratIzinApprove::whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))->count()),

            'processing' => Tab::make('Processing')
                ->query(fn ($query) => $query->whereHas('suratIzinApprove', fn ($q) => $q->where('status', 0)))
                ->badge(fn () => SuratIzinApprove::where('status', 0)
                    ->whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))
                    ->count()),

            'approved' => Tab::make('Approved')
                ->query(fn ($query) => $query->whereHas('suratIzinApprove', fn ($q) => $q->where('status', 1)))
                ->badge(fn () => SuratIzinApprove::where('status', 1)
                    ->whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))
                    ->count()),

            'rejected' => Tab::make('Rejected')
                ->query(fn ($query) => $query->whereHas('suratIzinApprove', fn ($q) => $q->where('status', 2)))
                ->badge(fn () => SuratIzinApprove::where('status', 2)
                    ->whereHas('suratIzin', fn ($query) => $query->where('user_id', $userId))
                    ->count()),
        ];
    }
}
