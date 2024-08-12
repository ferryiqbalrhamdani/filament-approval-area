<?php

namespace App\Filament\Resources\IzinLemburApproveDuaResource\Pages;

use App\Filament\Resources\IzinLemburApproveDuaResource;
use App\Models\IzinLemburApproveDua;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;


class ListIzinLemburApproveDuas extends ListRecords
{
    protected static string $resource = IzinLemburApproveDuaResource::class;

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
                ->badge(fn() => IzinLemburApproveDua::whereHas('izinLemburApprove.izinLembur.user', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })->count()),

            'processing' => Tab::make('Processing')
                ->query(
                    fn($query) => $query->where('status', 0)
                        ->whereHas('izinLemburApprove.izinLembur.user', function ($query) use ($companyId) {
                            $query->where('company_id', $companyId);
                        })
                )
                ->badge(fn() => IzinLemburApproveDua::where('status', 0)
                    ->whereHas('izinLemburApprove.izinLembur.user', function ($query) use ($companyId) {
                        $query->where('company_id', $companyId);
                    })
                    ->count()),

            'approved' => Tab::make('Approved')
                ->query(
                    fn($query) => $query->where('status', 1)
                        ->whereHas('izinLemburApprove.izinLembur.user', function ($query) use ($companyId) {
                            $query->where('company_id', $companyId);
                        })
                )
                ->badge(fn() => IzinLemburApproveDua::where('status', 1)
                    ->whereHas('izinLemburApprove.izinLembur.user', function ($query) use ($companyId) {
                        $query->where('company_id', $companyId);
                    })
                    ->count()),

            'rejected' => Tab::make('Rejected')
                ->query(
                    fn($query) => $query->where('status', 2)
                        ->whereHas('izinLemburApprove.izinLembur.user', function ($query) use ($companyId) {
                            $query->where('company_id', $companyId);
                        })
                )
                ->badge(fn() => IzinLemburApproveDua::where('status', 2)
                    ->whereHas('izinLemburApprove.izinLembur.user', function ($query) use ($companyId) {
                        $query->where('company_id', $companyId);
                    })
                    ->count()),
        ];
    }


    public function getDefaultActiveTab(): string | int | null
    {
        return 'processing';
    }
}
