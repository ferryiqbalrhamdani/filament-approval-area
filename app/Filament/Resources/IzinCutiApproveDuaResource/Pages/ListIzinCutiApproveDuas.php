<?php

namespace App\Filament\Resources\IzinCutiApproveDuaResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\IzinCutiApproveDuaResource;
use App\Models\IzinCutiApproveDua;

class ListIzinCutiApproveDuas extends ListRecords
{
    protected static string $resource = IzinCutiApproveDuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {

        return [
            null => Tab::make('All')
                ->badge(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApproveDua::whereHas('izinCutiApprove.userCuti', function ($query) use ($companyId) {
                        $query->where('company_id', $companyId); // Filter by company_id
                    })->count();
                }),

            'proccessing' => Tab::make('Processing')
                ->query(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApproveDua::whereHas('izinCutiApprove.userCuti', function ($query) use ($companyId) {
                        $query
                            ->where('company_id', $companyId); // Filter by company_id
                    })
                        ->where('status', 0);
                })
                ->badge(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApproveDua::whereHas('izinCutiApprove.userCuti', function ($query) use ($companyId) {
                        $query
                            ->where('company_id', $companyId); // Filter by company_id
                    })
                        ->where('status', 0)
                        ->count();
                }),
            'approved' => Tab::make('Approved')
                ->query(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApproveDua::whereHas('izinCutiApprove.userCuti', function ($query) use ($companyId) {
                        $query
                            ->where('company_id', $companyId); // Filter by company_id
                    })
                        ->where('status', 1);
                })
                ->badge(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApproveDua::whereHas('izinCutiApprove.userCuti', function ($query) use ($companyId) {
                        $query
                            ->where('company_id', $companyId); // Filter by company_id
                    })
                        ->where('status', 1)
                        ->count();
                }),
            'rejected' => Tab::make('Rejected')
                ->query(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApproveDua::whereHas('izinCutiApprove.userCuti', function ($query) use ($companyId) {
                        $query
                            ->where('company_id', $companyId); // Filter by company_id
                    })
                        ->where('status', 2);
                })
                ->badge(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApproveDua::whereHas('izinCutiApprove.userCuti', function ($query) use ($companyId) {
                        $query
                            ->where('company_id', $companyId); // Filter by company_id
                    })
                        ->where('status', 2)
                        ->count();
                }),

        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'proccessing';
    }
}
