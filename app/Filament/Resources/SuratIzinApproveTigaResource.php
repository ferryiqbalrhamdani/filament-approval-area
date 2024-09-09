<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\SuratIzinApproveTiga;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\ImageEntry;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\SuratIzinApproveTigaResource\Pages;
use App\Filament\Resources\SuratIzinApproveTigaResource\RelationManagers;
use App\Filament\Resources\SuratIzinApproveTigaResource\Widgets\SuratIzinApproveTigaStats;

class SuratIzinApproveTigaResource extends Resource
{
    protected static ?string $model = SuratIzinApproveTiga::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Tiga';

    protected static ?int $navigationSort = 30;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.user.first_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.user.company.slug')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.keperluan_izin')
                    ->label('Keperluan Izin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.lama_izin')
                    ->label('Lama Izin')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.tanggal_izin')
                    ->label('Tgl. Izin')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.sampai_tanggal')
                    ->label('Sampai Tgl. Izin')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.durasi_izin')
                    ->label('Durasi Izin')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.jam_izin')
                    ->label('Jam Izin')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('suratIzinApproveDua.suratIzinApprove.suratIzin.sampai_jam')
                    ->label('Sampai Jam')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),

                ViewColumn::make('status')
                    ->label('Status')
                    ->view('tables.columns.status-surat-izin')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter berdasarkan status
                Tables\Filters\Filter::make('status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                0 => 'Proccessing',
                                1 => 'Approved',
                                2 => 'Rejected',
                            ])
                            ->placeholder('Pilih Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Terapkan filter hanya jika 'status' diatur dan tidak kosong
                        if (isset($data['status']) && $data['status'] !== '') {
                            $query->where('status', $data['status']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        // Jika 'status' tidak diatur atau kosong, kembalikan indikator kosong
                        if (!isset($data['status']) || $data['status'] === '') {
                            return [];
                        }

                        $statusLabels = [
                            0 => 'Proccessing',
                            1 => 'Approved',
                            2 => 'Rejected',
                        ];

                        return ['Status: ' . $statusLabels[$data['status']]];
                    }),

                // Filter berdasarkan rentang tanggal izin
                Tables\Filters\Filter::make('tanggal_izin')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->placeholder('Pilih Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->placeholder('Pilih Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['start_date'], function ($query, $start) {
                                $query->whereHas('suratIzinApproveDua.suratIzinApprove.suratIzin', function ($query) use ($start) {
                                    $query->whereDate('tanggal_izin', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('suratIzinApproveDua.suratIzinApprove.suratIzin', function ($query) use ($end) {
                                    $query->whereDate('tanggal_izin', '<=', $end);
                                });
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Mulai dari: ' . $data['start_date'];
                        }

                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Sampai: ' . $data['end_date'];
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('Tahun')
                    ->form([
                        Forms\Components\Select::make('tanggal_izin')
                            ->label('Tahun')
                            ->options([
                                0 => 'Semua Tahun',
                                1 => 'Tahun Ini',
                            ])
                            ->default(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['tanggal_izin']) && $data['tanggal_izin'] === 1) {
                            $query->whereHas('suratIzinApproveDua.suratIzinApprove.suratIzin', function ($query) use ($data) {
                                $query->whereYear('tanggal_izin', Carbon::now()->year);
                            });
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_izin']) {
                            $indicators['tanggal_izin'] = 'Tahun: ' . Carbon::now()->year;
                        }

                        return $indicators;
                    }),

                // Tambahkan filter lainnya sesuai kebutuhan...
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (SuratIzinApproveTiga $record, array $data): void {


                            $record->update([
                                'status' => 0,
                                'keterangan' => null,
                                'user_id' => Auth::user()->id,
                            ]);
                            Notification::make()
                                ->title('Data berhasil di kembalikan')
                                ->success()
                                ->send();
                        })
                        ->visible(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Approve')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->action(function (SuratIzinApproveTiga $record, array $data): void {
                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);


                            Notification::make()
                                ->title('Data berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->color('success')
                        ->hidden(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Reject')
                        ->form([
                            Forms\Components\TextArea::make('keterangan')
                                // ->hiddenLabel()
                                ->required()
                                ->maxLength(255),
                        ])
                        ->requiresConfirmation()
                        ->icon('heroicon-o-x-circle')
                        ->action(function (SuratIzinApproveTiga $record, array $data): void {
                            $record->update([
                                'user_id' => Auth::user()->id,
                                'status' => 2,
                                'keterangan' => $data['keterangan'],
                            ]);
                            Notification::make()
                                ->title('Data berhasil di Reject')
                                ->success()
                                ->send();
                        })
                        ->color('danger')
                        ->hidden(fn($record) => $record->status > 0),
                ])
                    ->link()
                    ->label('Actions'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                Tables\Actions\BulkAction::make('Approve yang dipilih')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        foreach ($records as $record) {
                            $record->update([
                                'status' => 1,
                                'keterangan' => null,
                            ]);
                        }


                        Notification::make()
                            ->title('Data yang dipilih berhasil di Approve')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                ExportBulkAction::make()
                    ->label('Eksport data yang dipilih')
                    ->exports([
                        ExcelExport::make()
                            ->askForFilename()
                            ->askForWriterType()
                            ->withColumns([
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.user.first_name')
                                    ->heading('Nama User')
                                    ->formatStateUsing(fn($state, $record) => $record->suratIzinApproveDua->suratIzinApprove->suratIzin->user->first_name . ' ' . $record->suratIzinApproveDua->suratIzinApprove->suratIzin->user->last_name),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.user.company.name')
                                    ->heading('Perusahaan'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.user.jk')
                                    ->heading('Jenis Kelamin'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.keperluan_izin')
                                    ->heading('Keperluan Izin'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.lama_izin')
                                    ->heading('Lama Izin'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.tanggal_izin')
                                    ->heading('Tgl. Izin'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.sampai_tanggal')
                                    ->heading('Sampai Tgl. Izin'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.durasi_izin')
                                    ->heading('Durasi Izin'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.jam_izin')
                                    ->heading('Jam Izin'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.sampai_jam')
                                    ->heading('Sampai Jam'),
                                Column::make('suratIzinApproveDua.suratIzinApprove.suratIzin.keterangan_izin')
                                    ->heading('Keterangan Izin'),
                                Column::make('status')
                                    ->heading('Status')
                                    ->formatStateUsing(fn($state) => $state === 0 ? 'Diproses' : ($state === 1 ? 'Disetujui' : 'Ditolak')),
                                Column::make('keterangan')
                                    ->heading('Keterangan'),
                            ])
                    ])

            ])
            // ->checkIfRecordIsSelectableUsing(
            //     fn(SuratIzinApproveTiga $record): int => $record->status === 0,
            // )
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('Informasi User')
                            ->schema([
                                TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.user.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.user.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('suratIzinApproveDua.suratIzinApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('suratIzinApproveDua.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('suratIzinApproveDua.suratIzinApproveTiga.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Fieldset::make('Keterangan')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->hiddenlabel(),
                            ])->visible(fn($record) => $record->keterangan),
                    ]),
                Section::make()
                    ->schema([
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.keperluan_izin')
                            ->label('Keperluan Izin')
                            ->badge()
                            ->color('info')
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Tanggal')
                    ->schema([
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.lama_izin')
                            ->label('Lama Izin')
                            ->badge(),
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.tanggal_izin')
                            ->label('Tgl. Izin')
                            ->date(),
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.sampai_tanggal')
                            ->label('Sampai Tgl. Izin')
                            ->date(),
                    ])
                    ->columns(3),

                Fieldset::make('Lama Izin')
                    ->schema([
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.durasi_izin')
                            ->label('Durasi')
                            ->label('Durasi'),
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.jam_izin')
                            ->label('Jam Izin')
                            ->time('H:i'),
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.sampai_jam')
                            ->label('Sampai Jam')
                            ->time('H:i'),
                    ])
                    ->columns(3)
                    ->visible(fn(SuratIzinApproveTiga $record): string => $record->suratIzinApproveDua->suratIzinApprove->suratIzin->lama_izin === '1 Hari' && $record->suratIzinApproveDua->suratIzinApprove->suratIzin->durasi_izin),

                Fieldset::make('Keterangan Izin')
                    ->schema([
                        TextEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.keterangan_izin')
                            ->hiddenlabel()
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Bukti Foto')
                    ->schema([
                        ImageEntry::make('suratIzinApproveDua.suratIzinApprove.suratIzin.photo')
                            ->hiddenlabel()
                            ->width(800)
                            ->height(800)
                            ->size(800)
                            ->columnSpanFull(),
                    ])->visible(fn(SuratIzinApproveTiga $record): string => $record->suratIzinApproveDua->suratIzinApprove->suratIzin->photo !== null),
            ])
            ->columns(1);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuratIzinApproveTigas::route('/'),
            'create' => Pages\CreateSuratIzinApproveTiga::route('/create'),
            'edit' => Pages\EditSuratIzinApproveTiga::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 0)->count();
    }

    public static function getWidgets(): array
    {
        return [
            SuratIzinApproveTigaStats::class,
        ];
    }
}
