<?php

namespace App\Filament\Resources\IzinCutiApproveTigaResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Company;
use App\Models\IzinCutiApproveTiga;
use Filament\Resources\Components\Tab;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use App\Filament\Resources\IzinCutiApproveTigaResource;

class ListIzinCutiApproveTigas extends ListRecords
{
    protected static string $resource = IzinCutiApproveTigaResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //         ExportAction::make()

    //             ->label('Eksport data')
    //             ->exports([
    //                 ExcelExport::make()
    //                     ->askForFilename()
    //                     ->askForWriterType()
    //                     ->withColumns([
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.userCuti.first_name')
    //                             ->heading('Nama User')
    //                             ->formatStateUsing(fn($state, $record) => $record->izinCutiApproveDua->izinCutiApprove->userCuti->first_name . ' ' . $record->izinCutiApproveDua->izinCutiApprove->userCuti->last_name),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.userCuti.company.name')
    //                             ->heading('Perusahaan'),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.userCuti.jk')
    //                             ->heading('Jenis Kelamin'),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.keterangan_cuti')
    //                             ->heading('Keterangan Cuti'),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.pilihan_cuti')
    //                             ->heading('Pilihan Cuti'),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.mulai_cuti')
    //                             ->heading('Mulai Cuti'),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.sampai_cuti')
    //                             ->heading('Sampai Cuti'),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.lama_cuti')
    //                             ->heading('Lama Cuti'),
    //                         Column::make('izinCutiApproveDua.izinCutiApprove.pesan_cuti')
    //                             ->heading('Pesan Cuti'),
    //                         Column::make('status')
    //                             ->heading('Status')
    //                             ->formatStateUsing(fn($state) => $state === 0 ? 'Diproses' : ($state === 1 ? 'Disetujui' : 'Ditolak')),
    //                         Column::make('keterangan')
    //                             ->heading('Keterangan'),
    //                     ])
    //                     ->modifyQueryUsing(function ($query) {
    //                         // Calculate the 25th of the previous month and the 25th of the current month
    //                         $startDate = Carbon::now()->subMonth()->setDay(25)->startOfDay();
    //                         $endDate = Carbon::now()->setDay(25)->endOfDay();

    //                         return $query
    //                             ->join('izin_cuti_approve_duas', 'izin_cuti_approve_tigas.izin_cuti_approve_dua_id', '=', 'izin_cuti_approve_duas.id')
    //                             ->join('izin_cuti_approves', 'izin_cuti_approve_duas.izin_cuti_approve_id', '=', 'izin_cuti_approves.id')
    //                             ->whereBetween('izin_cuti_approves.mulai_cuti', [$startDate, $endDate]);
    //                     }),


    //             ])
    //     ];
    // }

    public function getTabs(): array
    {
        $data = [];

        // Add a tab for all data
        $data['all'] = Tab::make('All')
            ->modifyQueryUsing(fn(Builder $query) => $query)
            ->badge(fn() => IzinCutiApproveTiga::count());

        // Get companies, excluding specific slugs and names
        $companies = Company::where('slug', '!=', 'Tidak Ada')
            ->where('name', '!=', '-')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('izinCutiApproveDua.izinCutiApprove.userCuti', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                }))
                ->badge(fn() => IzinCutiApproveTiga::whereHas('izinCutiApproveDua.izinCutiApprove.userCuti', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                })->count());
        }

        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return IzinCutiApproveTigaResource::getWidgets();
    }
}
