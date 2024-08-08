<?php

namespace App\Filament\Resources\SuratIzinApproveResource\Pages;

use App\Filament\Resources\SuratIzinApproveResource;
use App\Models\SuratIzinApprove;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSuratIzinApproves extends ListRecords
{
    protected static string $resource = SuratIzinApproveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            null => Tab::make('All')
                ->badge(fn () => SuratIzinApprove::whereHas('suratIzin.user', fn ($query) => $query->where('company_id', $companyId))->count()),

            'processing' => Tab::make('Processing')
                ->query(fn ($query) => $query->where('status', 0))
                ->badge(fn () => SuratIzinApprove::where('status', 0)
                    ->whereHas('suratIzin.user', fn ($query) => $query->where('company_id', $companyId))
                    ->count()),

            'approved' => Tab::make('Approved')
                ->query(fn ($query) => $query->where('status', 1))
                ->badge(fn () => SuratIzinApprove::where('status', 1)
                    ->whereHas('suratIzin.user', fn ($query) => $query->where('company_id', $companyId))
                    ->count()),

            'rejected' => Tab::make('Rejected')
                ->query(fn ($query) => $query->where('status', 2))
                ->badge(fn () => SuratIzinApprove::where('status', 2)
                    ->whereHas('suratIzin.user', fn ($query) => $query->where('company_id', $companyId))
                    ->count()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'processing';
    }
}
