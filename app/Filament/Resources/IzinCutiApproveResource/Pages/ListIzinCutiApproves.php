<?php

namespace App\Filament\Resources\IzinCutiApproveResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\IzinCutiApproveResource;
use App\Models\IzinCutiApprove;

class ListIzinCutiApproves extends ListRecords
{
    protected static string $resource = IzinCutiApproveResource::class;

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

                    return IzinCutiApprove::whereHas('userCuti', function ($query) use ($companyId) {
                        $query->where('company_id', $companyId); // Filter by company_id
                    })->count();
                }),

            'proccessing' => Tab::make('Processing')
                ->query(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApprove::whereHas('userCuti', function ($query) use ($companyId) {
                        $query->where('status', 0)
                            ->where('company_id', $companyId); // Filter by company_id
                    });
                })
                ->badge(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApprove::whereHas('userCuti', function ($query) use ($companyId) {
                        $query->where('status', 0)
                            ->where('company_id', $companyId); // Filter by company_id
                    })->count();
                }),
            'approved' => Tab::make('Approved')
                ->query(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApprove::whereHas('userCuti', function ($query) use ($companyId) {
                        $query->where('status', 1)
                            ->where('company_id', $companyId); // Filter by company_id
                    });
                })
                ->badge(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApprove::whereHas('userCuti', function ($query) use ($companyId) {
                        $query->where('status', 1)
                            ->where('company_id', $companyId); // Filter by company_id
                    })->count();
                }),
            'rejected' => Tab::make('Rejected')
                ->query(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApprove::whereHas('userCuti', function ($query) use ($companyId) {
                        $query->where('status', 2)
                            ->where('company_id', $companyId); // Filter by company_id
                    });
                })
                ->badge(function () {
                    $companyId = Auth::user()->company_id; // Get the logged-in user's company_id

                    return IzinCutiApprove::whereHas('userCuti', function ($query) use ($companyId) {
                        $query->where('status', 2)
                            ->where('company_id', $companyId); // Filter by company_id
                    })->count();
                }),

        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'proccessing';
    }
}
