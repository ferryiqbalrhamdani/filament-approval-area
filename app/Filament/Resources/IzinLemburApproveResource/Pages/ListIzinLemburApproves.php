<?php

namespace App\Filament\Resources\IzinLemburApproveResource\Pages;

use App\Filament\Resources\IzinLemburApproveResource;
use App\Models\IzinLemburApprove;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;


class ListIzinLemburApproves extends ListRecords
{
    protected static string $resource = IzinLemburApproveResource::class;

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
                ->badge(fn() => IzinLemburApprove::whereHas('izinLembur.user', fn($query) => $query->where('company_id', $companyId))->count()),

            'processing' => Tab::make('Processing')
                ->query(fn($query) => $query->where('status', 0))
                ->badge(fn() => IzinLemburApprove::where('status', 0)
                    ->whereHas('izinLembur.user', fn($query) => $query->where('company_id', $companyId))
                    ->count()),

            'approved' => Tab::make('Approved')
                ->query(fn($query) => $query->where('status', 1))
                ->badge(fn() => IzinLemburApprove::where('status', 1)
                    ->whereHas('izinLembur.user', fn($query) => $query->where('company_id', $companyId))
                    ->count()),

            'rejected' => Tab::make('Rejected')
                ->query(fn($query) => $query->where('status', 2))
                ->badge(fn() => IzinLemburApprove::where('status', 2)
                    ->whereHas('izinLembur.user', fn($query) => $query->where('company_id', $companyId))
                    ->count()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'processing';
    }
}
