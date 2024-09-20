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
        return [
            null => Tab::make('All')
                ->badge(fn() => IzinLemburApprove::where('user_id', Auth::user()->id)->count()),

            'proccessing' => Tab::make('Proccessing')
                ->query(
                    fn($query) => $query->where('user_id', Auth::user()->id)
                        ->where('status', 0)
                )
                ->badge(fn() => IzinLemburApprove::where('user_id', Auth::user()->id)
                    ->where('status', 0)
                    ->count()),

            'approved' => Tab::make('Approved')
                ->query(
                    fn($query) => $query->where('user_id', Auth::user()->id)
                        ->where('status', 1)
                )
                ->badge(fn() => IzinLemburApprove::where('user_id', Auth::user()->id)
                    ->where('status', 1)
                    ->count()),

            'rejected' => Tab::make('Rejected')
                ->query(
                    fn($query) => $query->where('user_id', Auth::user()->id)
                        ->where('status', 2)
                )
                ->badge(fn() => IzinLemburApprove::where('user_id', Auth::user()->id)
                    ->where('status', 2)
                    ->count()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'proccessing';
    }
}
