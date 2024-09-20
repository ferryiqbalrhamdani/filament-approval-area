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
        return [
            null => Tab::make('All')
                ->badge(fn() => SuratIzinApproveDua::where('user_id', Auth::user()->id)->count()),

            'proccessing' => Tab::make('Proccessing')
                ->query(
                    fn($query) => $query->where('user_id', Auth::user()->id)
                        ->where('status', 0)
                )
                ->badge(fn() => SuratIzinApproveDua::where('user_id', Auth::user()->id)
                    ->where('status', 0)
                    ->count()),

            'approved' => Tab::make('Approved')
                ->query(
                    fn($query) => $query->where('user_id', Auth::user()->id)
                        ->where('status', 1)
                )
                ->badge(fn() => SuratIzinApproveDua::where('user_id', Auth::user()->id)
                    ->where('status', 1)
                    ->count()),

            'rejected' => Tab::make('Rejected')
                ->query(
                    fn($query) => $query->where('user_id', Auth::user()->id)
                        ->where('status', 2)
                )
                ->badge(fn() => SuratIzinApproveDua::where('user_id', Auth::user()->id)
                    ->where('status', 2)
                    ->count()),
        ];
    }


    public function getDefaultActiveTab(): string | int | null
    {
        return 'proccessing';
    }
}
