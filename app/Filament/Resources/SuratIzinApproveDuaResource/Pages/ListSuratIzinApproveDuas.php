<?php

namespace App\Filament\Resources\SuratIzinApproveDuaResource\Pages;

use App\Filament\Resources\SuratIzinApproveDuaResource;
use App\Models\SuratIzinApproveDua;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSuratIzinApproveDuas extends ListRecords
{
    protected static string $resource = SuratIzinApproveDuaResource::class;

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
                ->badge(fn () => SuratIzinApproveDua::whereHas('suratIzinApprove.suratIzin.user', fn ($query) => $query->where('company_id', $companyId))->count()),

            'processing' => Tab::make('Processing')
                ->query(fn ($query) => $query->where('status', 0))
                ->badge(fn () => SuratIzinApproveDua::where('status', 0)
                    ->whereHas('suratIzin.user', fn ($query) => $query->where('company_id', $companyId))
                    ->count()),

            'approved' => Tab::make('Approved')
                ->query(fn ($query) => $query->where('status', 1))
                ->badge(fn () => SuratIzinApproveDua::where('status', 1)
                    ->whereHas('suratIzinApprove.suratIzin.user', fn ($query) => $query->where('company_id', $companyId))
                    ->count()),

            'rejected' => Tab::make('Rejected')
                ->query(fn ($query) => $query->where('status', 2))
                ->badge(fn () => SuratIzinApproveDua::where('status', 2)
                    ->whereHas('suratIzinApprove.suratIzin.user', fn ($query) => $query->where('company_id', $companyId))
                    ->count()),
        ];
    }
}
