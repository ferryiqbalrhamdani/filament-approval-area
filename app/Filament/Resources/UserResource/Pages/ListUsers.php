<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Company;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $data = [];

        // Tambahkan tab untuk semua data
        $data['all'] = Tab::make('All Data')->modifyQueryUsing(fn (Builder $query) => $query);

        $companies = Company::orderBy('name', 'asc')->get();
        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('company_id', $company->id));
        }

        return $data;
    }
}
