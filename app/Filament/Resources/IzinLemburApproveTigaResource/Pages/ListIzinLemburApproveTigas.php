<?php

namespace App\Filament\Resources\IzinLemburApproveTigaResource\Pages;

use App\Filament\Resources\IzinLemburApproveTigaResource;
use App\Models\Company;
use App\Models\IzinLemburApproveTiga;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListIzinLemburApproveTigas extends ListRecords
{
    protected static string $resource = IzinLemburApproveTigaResource::class;

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
            ->badge(fn() => IzinLemburApproveTiga::count());

        // Get companies, excluding specific slugs and names
        $companies = Company::where('slug', '!=', 'Tidak Ada')
            ->where('name', '!=', '-')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('izinLemburApproveDua.izinLemburApprove.izinLembur.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                }))
                ->badge(fn() => IzinLemburApproveTiga::whereHas('izinLemburApproveDua.izinLemburApprove.izinLembur.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                })->count());
        }

        return $data;
    }
}
