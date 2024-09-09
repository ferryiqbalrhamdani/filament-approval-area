<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\IzinCutiApproveTiga;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (IzinCutiApproveTiga $record, array $data): void {
                            $izinCuti = $record->izinCutiApproveDua->izinCutiApprove;

                            if ($izinCuti->keterangan_cuti == 'Cuti Pribadi') {
                                $lamaCuti = explode(' ', $izinCuti->lama_cuti);
                                $sisaCuti = $izinCuti->userCuti->cuti + (int)$lamaCuti[0];

                                $izinCuti->userCuti->update([
                                    'cuti' => $sisaCuti,
                                ]);
                            }

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
                        ->action(function (IzinCutiApproveTiga $record, array $data): void {
                            $izinCuti = $record->izinCutiApproveDua->izinCutiApprove;

                            if ($izinCuti->keterangan_cuti == 'Cuti Pribadi') {
                                if ($izinCuti->userCuti->cuti > 0) {
                                    $lamaCuti = explode(' ', $izinCuti->lama_cuti);
                                    $sisaCuti = $izinCuti->userCuti->cuti - (int)$lamaCuti[0];

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

                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(IzinCutiApproveTiga $record): int => $record->status === 0,
            );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
