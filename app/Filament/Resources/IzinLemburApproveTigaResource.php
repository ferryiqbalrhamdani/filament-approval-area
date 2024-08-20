<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\TarifLembur;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Models\IzinLemburApproveTiga;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Tables\Columns\Summarizers\Count;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IzinLemburApproveTigaResource\Pages;
use App\Filament\Resources\IzinLemburApproveTigaResource\RelationManagers;

class IzinLemburApproveTigaResource extends Resource
{
    protected static ?string $model = IzinLemburApproveTiga::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Tiga';

    protected static ?int $navigationSort = 32;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.user.first_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tanggal_lembur')
                    ->label('Tanggal Lembur')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.start_time')
                    ->label('Start Time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.end_time')
                    ->label('End Time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.lama_lembur')
                    ->label('Lama Lembur')
                    ->badge()
                    ->suffix(' Jam')
                    ->sortable()
                    ->searchable(),
                // ViewColumn::make('izinLemburApproveDua.izinLemburApprove.status')
                //     ->view('tables.columns.status-surat-izin')
                //     ->label('Status Satu')
                //     ->alignment(Alignment::Center)
                //     ->sortable()
                //     ->searchable(),
                // ViewColumn::make('izinLemburApproveDua.status')
                //     ->view('tables.columns.status-surat-izin')
                //     ->label('Status Dua')
                //     ->alignment(Alignment::Center)
                //     ->sortable()
                //     ->searchable(),
                ViewColumn::make('status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.status_hari')
                    ->label('Status Hari')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Weekday' => 'warning',
                        'Weekend' => 'success',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.tarif_lembur_perjam')
                    ->label('Upah Per Jam')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.uang_makan')
                    ->label('Uang Makan')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.tarif_lumsum')
                    ->label('Lumsum')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApproveDua.izinLemburApprove.izinLembur.total')
                    ->label('Total')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->color('success')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
                Tables\Filters\Filter::make('tanggal_lembur')
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
                                $query->whereHas('izinLemburApproveDua.izinLemburApprove.izinLembur', function ($query) use ($start) {
                                    $query->whereDate('tanggal_lembur', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('izinLemburApproveDua.izinLemburApprove.izinLembur', function ($query) use ($end) {
                                    $query->whereDate('tanggal_lembur', '<=', $end);
                                });
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Tanggal Mulai: ' . Carbon::parse($data['start_date'])->toFormattedDateString();
                        }

                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Tanggal Akhir: ' . Carbon::parse($data['end_date'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('Tahun')
                    ->form([
                        Forms\Components\Select::make('tanggal_lembur')
                            ->label('Tahun')
                            ->options([
                                0 => 'Semua Tahun',
                                1 => 'Tahun Ini',
                            ])
                            ->default(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['tanggal_lembur']) && $data['tanggal_lembur'] === 1) {
                            $query->whereHas('izinLemburApproveDua.izinLemburApprove.izinLembur', function ($query) use ($data) {
                                $query->whereYear('tanggal_lembur', Carbon::now()->year);
                            });
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_lembur']) {
                            $indicators['tanggal_lembur'] = 'Tahun: ' . Carbon::now()->year;
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (IzinLemburApproveTiga $record, array $data): void {

                            // Retrieve the related models step by step
                            $izinLemburApproveDua = $record->izinLemburApproveDua;
                            $izinLemburApprove = $izinLemburApproveDua->izinLemburApprove;
                            $izinLembur = $izinLemburApprove->izinLembur;

                            // Dump the retrieved $izinLembur object to inspect it
                            $izinLembur->update([
                                'total' => 0
                            ]);

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
                        ->action(function (IzinLemburApproveTiga $record, array $data): void {
                            $izinLembur = $record->izinLemburApproveDua->izinLemburApprove->izinLembur;
                            $tarifLembur = $izinLembur->tarifLembur;

                            if ($tarifLembur->is_lumsum === true) {
                                $total = $tarifLembur->tarif_lumsum;
                            } else {
                                $total = ($tarifLembur->tarif_lembur_perjam * $izinLembur->lama_lembur) + $tarifLembur->uang_makan;
                            }


                            $izinLembur->update([
                                'total' => $total
                            ]);


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
                        ->action(function (IzinLemburApproveTiga $record, array $data): void {
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
            ->checkIfRecordIsSelectableUsing(
                fn(IzinLemburApproveTiga $record): int => $record->status === 0,
            )
            ->recordAction(null)
            ->recordUrl(null)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Approve yang dipilih')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $izinLembur = $record->izinLemburApproveDua->izinLemburApprove->izinLembur;
                                $tarifLembur = $izinLembur->tarifLembur;

                                if ($tarifLembur->is_lumsum === true) {
                                    $total = $tarifLembur->tarif_lumsum;
                                } else {
                                    $total = ($tarifLembur->tarif_lembur_perjam * $izinLembur->lama_lembur) + $tarifLembur->uang_makan;
                                }


                                $izinLembur->update([
                                    'total' => $total
                                ]);

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
                ]),
            ])
        ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('Informasi User')
                            ->schema([
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.user.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.user.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('izinLemburApproveDua.izinLemburApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('izinLemburApproveDua.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Fieldset::make('Keterangan')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->hiddenlabel(),
                            ])->visible(fn($record) => $record->keterangan),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tanggal_lembur')
                                    ->label('Tgl Lembur')
                                    ->date(),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.start_time')
                                    ->label('Start Time')
                                    ->time('H:i'),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.end_time')
                                    ->label('End Time')
                                    ->time('H:i'),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.lama_lembur')
                                    ->label('Lama Lembur')
                                    ->suffix(' Jam')
                                    ->badge(),
                            ])
                            ->columns(4),

                        Fieldset::make('Keterangan Izin')
                            ->schema([
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.keterangan_lembur')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),

                        Fieldset::make('Perhitungan Lembur')
                            ->schema([
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.status_hari')
                                    ->badge()
                                    ->label('Status Hari')
                                    ->columnSpanFull(),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.lama_lembur')
                                    ->label('Lama Lembur')
                                    ->suffix(' Jam'),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.tarif_lembur_perjam')
                                    ->label('Tarif Lembur Per Jam')
                                    ->money(
                                        'IDR',
                                        locale: 'id'
                                    ),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.tarifLembur.uang_makan')
                                    ->label('Uang Makan')
                                    ->money(
                                        'IDR',
                                        locale: 'id'
                                    ),
                                TextEntry::make('izinLemburApproveDua.izinLemburApprove.izinLembur.total')
                                    ->label('Total')
                                    ->money(
                                        'IDR',
                                        locale: 'id'
                                    ),

                            ])
                            ->columns(4),
                    ])
            ])
            ->columns(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIzinLemburApproveTigas::route('/'),
            'create' => Pages\CreateIzinLemburApproveTiga::route('/create'),
            'edit' => Pages\EditIzinLemburApproveTiga::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 0)->count();
    }
}
