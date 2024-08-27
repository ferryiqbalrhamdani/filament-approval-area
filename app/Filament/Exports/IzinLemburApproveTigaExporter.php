<?php

namespace App\Filament\Exports;

use App\Models\IzinLemburApproveTiga;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class IzinLemburApproveTigaExporter extends Exporter
{
    protected static ?string $model = IzinLemburApproveTiga::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.user.first_name')
                ->label('Nama User'),
            ExportColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tanggal_lembur')
                ->label('Tgl. Lembur'),
            ExportColumn::make('status'),
            ExportColumn::make('keterangan'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your izin lembur approve tiga export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
