<?php

namespace App\Filament\Resources\SuratIzinApproveTigaResource\Pages;

use App\Filament\Resources\SuratIzinApproveTigaResource;
use App\Models\Company;
use App\Models\SuratIzinApproveTiga;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSuratIzinApproveTigas extends ListRecords
{
    protected static string $resource = SuratIzinApproveTigaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $data = [];

        // Add a tab for all data
        $data['all'] = Tab::make('All')
            ->modifyQueryUsing(fn(Builder $query) => $query)
            ->badge(fn() => SuratIzinApproveTiga::count());

        // Get companies, excluding specific slugs and names
        $companies = Company::where('slug', '!=', 'Tidak Ada')
            ->where('name', '!=', '-')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('suratIzinApproveDua.suratIzinApprove.suratIzin.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                }))
                ->badge(fn() => SuratIzinApproveTiga::whereHas('suratIzinApproveDua.suratIzinApprove.suratIzin.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                })->count());
        }

        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return SuratIzinApproveTigaResource::getWidgets();
    }
}
