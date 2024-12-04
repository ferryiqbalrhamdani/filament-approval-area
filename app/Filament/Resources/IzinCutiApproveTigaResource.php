<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\IzinCutiApproveTiga;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\BulkAction;
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
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\IzinCutiApproveTigaResource\Pages;
use App\Filament\Resources\IzinCutiApproveTigaResource\RelationManagers;
use App\Filament\Resources\IzinCutiApproveTigaResource\Widgets\IzinCutiApproveTigaStats;

class IzinCutiApproveTigaResource extends Resource
{
    protected static ?string $model = IzinCutiApproveTiga::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Tiga';

    protected static ?int $navigationSort = 31;

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
                Tables\Columns\TextColumn::make('izinCutiApproveDua.izinCutiApprove.userCuti.first_name')
                    ->label('Nama User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApproveDua.izinCutiApprove.userCuti.company.slug')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinCutiApproveDua.izinCutiApprove.keterangan_cuti')
                    ->label('Keterangan Cuti')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApproveDua.izinCutiApprove.mulai_cuti')
                    ->label('Mulai Cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApproveDua.izinCutiApprove.sampai_cuti')
                    ->label('Sampai Cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApproveDua.izinCutiApprove.lama_cuti')
                    ->label('Lama Cuti'),

                ViewColumn::make('status')
                    ->view('tables.columns.status-surat-izin')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinCutiApproveDua.izinCutiApprove.created_at')
                    ->label('Created At')
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
                Tables\Filters\Filter::make('mulai_cuti')
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
                                $query->whereHas('izinCutiApproveDua.izinCutiApprove', function ($query) use ($start) {
                                    $query->whereDate('mulai_cuti', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('izinCutiApproveDua.izinCutiApprove', function ($query) use ($end) {
                                    $query->whereDate('mulai_cuti', '<=', $end);
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
                        Forms\Components\Select::make('mulai_cuti')
                            ->label('Tahun')
                            ->options([
                                0 => 'Semua Tahun',
                                1 => 'Tahun Ini',
                            ])
                            ->default(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['mulai_cuti']) && $data['mulai_cuti'] === 1) {
                            $query->whereHas('izinCutiApproveDua.izinCutiApprove', function ($query) use ($data) {
                                $query->whereYear('mulai_cuti', Carbon::now()->year);
                            });
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['mulai_cuti']) {
                            $indicators['mulai_cuti'] = 'Tahun: ' . Carbon::now()->year;
                        }

                        return $indicators;
                    }),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),
                    // Tables\Actions\Action::make('Kembalikan Data')
                    //     ->color('gray')
                    //     ->icon('heroicon-o-arrow-uturn-left')
                    //     ->requiresConfirmation()
                    //     ->action(function (IzinCutiApproveTiga $record, array $data): void {
                    //         $izinCuti = $record->izinCutiApproveDua->izinCutiApprove;

                    //         $leaveMonth = \Carbon\Carbon::parse($izinCuti->tanggal_mulai)->format('Y-m');
                    //         $currentMonth = now()->format('Y-m');
                    //         if ($leaveMonth > $currentMonth) {
                    //             Notification::make()
                    //                 ->title('Error')
                    //                 ->body('Cuti tidak dapat dikembalikan karena tanggal cuti melewati bulan ini.')
                    //                 ->danger()
                    //                 ->send();
                    //             return;
                    //         }

                    //         if ($izinCuti->keterangan_cuti == 'Cuti Pribadi') {
                    //             if ($record->status == 2) {
                    //                 $lamaCuti = explode(' ', $izinCuti->lama_cuti);
                    //                 $sisaCuti = $izinCuti->userCuti->cuti - (int)$lamaCuti[0];

                    //                 if ($sisaCuti > 6) {
                    //                     Notification::make()
                    //                         ->title('Error')
                    //                         ->body('Jumlah izin cuti melebihi batas maksimal 6 hari.')
                    //                         ->danger()
                    //                         ->send();
                    //                     return;
                    //                 }

                    //                 $izinCuti->userCuti->update([
                    //                     'cuti' => $sisaCuti,
                    //                 ]);
                    //             }
                    //         }

                    //         $record->update([
                    //             'status' => 0,
                    //             'keterangan' => null,
                    //             'user_id' => Auth::user()->id,
                    //         ]);

                    //         Notification::make()
                    //             ->title('Data berhasil di kembalikan')
                    //             ->success()
                    //             ->send();
                    //     })
                    //     ->visible(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Approve')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->action(function (IzinCutiApproveTiga $record, array $data): void {
                            $izinCuti = $record->izinCutiApproveDua->izinCutiApprove;

                            if ($izinCuti->keterangan_cuti == 'Cuti Pribadi') {
                                if ($izinCuti->userCuti->cuti > 0) {
                                    $sisaCuti = $izinCuti->userCuti->cuti;

                                    if ($sisaCuti < 0) {
                                        Notification::make()
                                            ->title('Cuti yang diinput melebihi batas cuti yang tersedia')
                                            ->danger()
                                            ->send();
                                    } else {
                                        $record->update([
                                            'status' => 1,
                                            'user_id' => Auth::user()->id,
                                        ]);

                                        $izinCuti->userCuti->update([
                                            'cuti' => $sisaCuti,
                                        ]);

                                        Notification::make()
                                            ->title('Data berhasil di Approve')
                                            ->success()
                                            ->send();
                                    }
                                } else {
                                    Notification::make()
                                        ->title('Batas sisa cuti sudah habis')
                                        ->danger()
                                        ->send();
                                }
                            } else {
                                $record->update([
                                    'status' => 1,
                                    'user_id' => Auth::user()->id,
                                ]);

                                Notification::make()
                                    ->title('Batas sisa cuti sudah habis')
                                    ->danger()
                                    ->send();
                            }
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
                        ->action(function (IzinCutiApproveTiga $record, array $data): void {
                            $izinCuti = $record->izinCutiApproveDua->izinCutiApprove;

                            // Check if the leave date is beyond the current month
                            $leaveMonth = \Carbon\Carbon::parse($izinCuti->tanggal_mulai)->format('Y-m');
                            $currentMonth = now()->format('Y-m');
                            if ($leaveMonth > $currentMonth) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Cuti tidak dapat di-reject karena tanggal cuti melewati bulan ini.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Calculate leave and check if it exceeds 6 days
                            if ($izinCuti->keterangan_cuti == 'Cuti Pribadi') {
                                $lamaCuti = explode(' ', $izinCuti->lama_cuti);
                                $sisaCuti = $izinCuti->userCuti->cuti + (int)$lamaCuti[0];

                                if ($sisaCuti > 6) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body('Jumlah izin cuti melebihi batas maksimal 6 hari.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $izinCuti->userCuti->update([
                                    'cuti' => $sisaCuti,
                                ]);
                            }

                            // Update record after passing all checks
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
            ->groups([
                Tables\Grouping\Group::make('izinCutiApproveDua.izinCutiApprove.userCuti.first_name')
                    ->label('Nama User')
                    ->collapsible(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Approve yang dipilih')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $successCount = 0;
                            $errorMessages = [];

                            foreach ($records as $record) {
                                $izinCuti = $record->izinCutiApproveDua->izinCutiApprove;

                                if ($izinCuti->keterangan_cuti == 'Cuti Pribadi') {
                                    if ($izinCuti->userCuti->cuti > 0) {
                                        $lamaCuti = explode(' ', $izinCuti->lama_cuti);
                                        $sisaCuti = $izinCuti->userCuti->cuti - (int)$lamaCuti[0];

                                        if ($sisaCuti < 0) {
                                            $errorMessages[] = 'Cuti yang diinput melebihi batas cuti yang tersedia untuk user ' . $izinCuti->userCuti->first_name;
                                        } else {
                                            $record->update([
                                                'status' => 1,
                                                'user_id' => Auth::user()->id,
                                            ]);

                                            $izinCuti->userCuti->update([
                                                'cuti' => $sisaCuti,
                                            ]);

                                            $successCount++;
                                        }
                                    } else {
                                        $errorMessages[] = 'Batas sisa cuti sudah habis untuk user ' . $izinCuti->userCuti->first_name;
                                    }
                                } else {
                                    $record->update([
                                        'status' => 1,
                                        'user_id' => Auth::user()->id,
                                    ]);

                                    $successCount++;
                                }
                            }

                            if ($successCount > 0) {
                                Notification::make()
                                    ->title("$successCount data berhasil di Approve")
                                    ->success()
                                    ->send();
                            }

                            if (!empty($errorMessages)) {
                                Notification::make()
                                    ->title('Beberapa data gagal di Approve')
                                    ->body(implode("\n", $errorMessages))
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->label('Eksport Excel')
                        ->exports([
                            ExcelExport::make()
                                ->askForFilename()
                                ->askForWriterType()
                                ->withColumns([
                                    Column::make('izinCutiApproveDua.izinCutiApprove.userCuti.first_name')
                                        ->heading('Nama User')
                                        ->formatStateUsing(fn($state, $record) => $record->izinCutiApproveDua->izinCutiApprove->userCuti->first_name . ' ' . $record->izinCutiApproveDua->izinCutiApprove->userCuti->last_name),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.userCuti.company.name')
                                        ->heading('Perusahaan'),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.userCuti.jk')
                                        ->heading('Jenis Kelamin'),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.keterangan_cuti')
                                        ->heading('Keterangan Cuti'),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.pilihan_cuti')
                                        ->heading('Pilihan Cuti'),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.mulai_cuti')
                                        ->heading('Mulai Cuti'),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.sampai_cuti')
                                        ->heading('Sampai Cuti'),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.lama_cuti')
                                        ->heading('Lama Cuti'),
                                    Column::make('izinCutiApproveDua.izinCutiApprove.pesan_cuti')
                                        ->heading('Pesan Cuti'),
                                    Column::make('status')
                                        ->heading('Status')
                                        ->formatStateUsing(fn($state) => $state === 0 ? 'Diproses' : ($state === 1 ? 'Disetujui' : 'Ditolak')),
                                    Column::make('keterangan')
                                        ->heading('Keterangan'),
                                ])
                        ]),
                    BulkAction::make('export_pdf')
                        ->label('Export PDF')
                        ->action(function (Collection $records) {
                            return static::exportToPDF($records);
                        })
                        ->icon('heroicon-o-arrow-down-tray'),
                ]),
            ]);
    }

    public static function exportToPDF(Collection $records)
    {
        // Load view dan generate PDF
        $pdf = Pdf::loadView('pdf.export', ['records' => $records]);

        // Return PDF sebagai response download
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'data-cuti-' . Carbon::now() . '.pdf'
        );
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
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.userCuti.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.userCuti.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('izinCutiApproveDua.izinCutiApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('izinCutiApproveDua.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('izinCutiApproveDua.izinCutiApprove.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApproveTiga $record) => optional(optional(optional($record)->izinCutiApproveDua)->izinCutiApprove)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApproveTiga $record) => optional(optional($record)->izinCutiApproveDua)->status === 2),
                                        TextEntry::make('user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApproveTiga $record) => optional($record)->status === 2),
                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('izinCutiApproveDua.izinCutiApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApproveTiga $record) => optional(optional(optional($record)->izinCutiApproveDua)->izinCutiApprove)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApproveTiga $record) => optional(optional($record)->izinCutiApproveDua)->status === 2),
                                        TextEntry::make('keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApproveTiga $record) => optional($record)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn(IzinCutiApproveTiga $record) =>
                                optional(optional($record)->izinCutiApprove)->status === 2 ||
                                    optional($record)->status === 2 ||
                                    optional(optional($record)->izinCutiApproveTiga)->status === 2
                            ),
                        Section::make()
                            ->schema([
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.keterangan_cuti')
                                    ->label('Keterangan Cuti')
                                    ->badge()
                                    ->color('gray')
                                    ->columnSpanFull(),
                            ]),
                        Section::make()
                            ->schema([
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.pilihan_cuti')
                                    ->label('Pilihan Cuti')
                                    ->badge()
                                    ->color('info')
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn(IzinCutiApproveTiga $record) => optional(optional(optional($record)->izinCutiApproveDua)->izinCutiApprove)->keterangan_cuti === 'Cuti Khusus'),
                        Fieldset::make('Tanggal')
                            ->schema([
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.mulai_cuti')
                                    ->label('Mulai Cuti')
                                    ->date(),
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.sampai_cuti')
                                    ->label('Sampai Cuti')
                                    ->date(),
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.lama_cuti')
                                    ->label('Lama Cuti')
                                    ->badge(),
                            ])
                            ->columns(3),
                        Fieldset::make('Keterangan Cuti')
                            ->schema([
                                TextEntry::make('izinCutiApproveDua.izinCutiApprove.pesan_cuti')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function getWidgets(): array
    {
        return [
            IzinCutiApproveTigaStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIzinCutiApproveTigas::route('/'),
            'create' => Pages\CreateIzinCutiApproveTiga::route('/create'),
            'edit' => Pages\EditIzinCutiApproveTiga::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 0)->count();
    }
}
