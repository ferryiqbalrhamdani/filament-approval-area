<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\IzinCutiApproveDua;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IzinCutiApproveDuaResource\Pages;
use App\Filament\Resources\IzinCutiApproveDuaResource\RelationManagers;

class IzinCutiApproveDuaResource extends Resource
{
    protected static ?string $model = IzinCutiApproveDua::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Dua';

    protected static ?int $navigationSort = 21;

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
                Tables\Columns\TextColumn::make('izinCutiApprove.userCuti.first_name')
                    ->label('Nama User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.keterangan_cuti')
                    ->label('Keterangan Cuti')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.mulai_cuti')
                    ->label('Mulai Cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.sampai_cuti')
                    ->label('Sampai Cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.lama_cuti')
                    ->label('Lama Cuti'),
                ViewColumn::make('izinCutiApprove.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinCutiApproveTiga.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Tiga')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
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
                                $query->whereHas('izinCutiApprove', function ($query) use ($start) {
                                    $query->whereDate('mulai_cuti', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('izinCutiApprove', function ($query) use ($end) {
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
                            $query->whereHas('izinCutiApprove', function ($query) use ($data) {
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
            ->defaultSort('created_at', 'desc')
            ->checkIfRecordIsSelectableUsing(
                fn(IzinCutiApproveDua $record): int => $record->status === 0,
            )
            ->recordAction(null)
            ->recordUrl(null)
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (IzinCutiApproveDua $record, array $data): void {
                            // Hapus data di IzinCutiApproveDua jika ada dan statusnya 0

                            $record->update([
                                'status' => 0,
                                'keterangan' => null,
                                'user_id' => Auth::user()->id,
                            ]);

                            $record->IzinCutiApproveTiga()
                                ->where('status', 0)
                                ->delete();

                            Notification::make()
                                ->title('Data berhasil di kembalikan')
                                ->success()
                                ->send();
                        })
                        ->visible(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Approve')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->action(function (IzinCutiApproveDua $record, array $data): void {
                            // Update the status of the selected record
                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);

                            // Create the related IzinCutiApproveDua record with the correct foreign key
                            $record->izinCutiApproveTiga()->create([
                                'izin_cuti_approve_dua_id' => $record->id,  // Correctly reference the current record's ID
                            ]);

                            // Send success notification
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
                        ->action(function (IzinCutiApproveDua $record, array $data): void {
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
                            foreach ($records as $record) {

                                $record->update([
                                    'status' => 1,
                                    'keterangan' => null,
                                ]);

                                $record->izinCutiApproveTiga()->create([
                                    'izin_cuti_approve_id' => $record->id,  // Correctly reference the current record's ID
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
            ->query(function (IzinCutiApproveDua $query) {
                return $query->where(function ($query) {
                    $query->whereHas('izinCutiApprove.cutiKhusus.user', function ($query) {
                        $query->where('company_id', Auth::user()->company_id);
                    })
                        ->orWhereHas('izinCutiApprove.cutiPribadi.user', function ($query) {
                            $query->where('company_id', Auth::user()->company_id);
                        });
                });
            });
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
            'index' => Pages\ListIzinCutiApproveDuas::route('/'),
            'create' => Pages\CreateIzinCutiApproveDua::route('/create'),
            'edit' => Pages\EditIzinCutiApproveDua::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        $count = $modelClass::where('status', 0)
            ->whereHas('izinCutiApprove.userCuti', function (Builder $query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->count();

        return (string) $count;
    }
}
