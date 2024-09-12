<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\IzinCutiApprove;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\IzinCutiApproveDua;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IzinCutiApproveResource\Pages;
use App\Filament\Resources\IzinCutiApproveResource\RelationManagers;

class IzinCutiApproveResource extends Resource
{
    protected static ?string $model = IzinCutiApprove::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Satu';

    protected static ?int $navigationSort = 11;

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
                Tables\Columns\TextColumn::make('userCuti.first_name')
                    ->label('Nama User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan_cuti')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mulai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sampai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lama_cuti')
                    ->label('Lama Cuti'),
                ViewColumn::make('status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinCutiApproveDua.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinCutiApproveDua.izinCutiApproveTiga.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Tiga')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
                                $query->whereDate('mulai_cuti', '>=', $start);
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereDate('mulai_cuti', '<=', $end);
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

                Tables\Filters\Filter::make('keterangan_cuti')
                    ->form([
                        Forms\Components\Select::make('keterangan_cuti')
                            ->label('Keterangan Cuti')
                            ->options([
                                'Cuti Khusus' => 'Cuti Khusus',
                                'Cuti Pribadi' => 'Cuti Pribadi',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['keterangan_cuti'] ?? null, // Check if 'keterangan_cuti' exists
                            function ($query, $value) { // Apply the query
                                return $query->where('keterangan_cuti', $value);
                            }
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['keterangan_cuti'] ?? null) {
                            $indicators['keterangan_cuti'] = 'Keterangan Cuti: ' . $data['keterangan_cuti'];
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
                            $query->whereYear('mulai_cuti', Carbon::now()->year);
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
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (IzinCutiApprove $record, array $data): void {
                            // Hapus data di IzinCutiApproveDua jika ada dan statusnya 0

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
                        ->action(function (IzinCutiApprove $record, array $data): void {
                            if (!is_null($record->cuti_pribadi_id)) {
                                // Process Cuti Pribadi
                                $record->update([
                                    'status' => 1,
                                    'user_id' => Auth::user()->id,
                                ]);
                            } elseif (!is_null($record->cuti_khusus_id)) {
                                // Process Cuti Khusus
                                $record->update([
                                    'status' => 1,
                                    'user_id' => Auth::user()->id,
                                ]);
                            }

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
                        ->action(function (IzinCutiApprove $record, array $data): void {
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
                                if (!is_null($record->cuti_pribadi_id)) {
                                    // Process Cuti Pribadi
                                    $record->update([
                                        'status' => 1,
                                        'keterangan' => null,
                                    ]);
                                } elseif (!is_null($record->cuti_khusus_id)) {
                                    // Process Cuti Khusus
                                    $record->update([
                                        'status' => 1,
                                        'keterangan' => null,
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('Data yang dipilih berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(IzinCutiApprove $record): int => $record->status === 0,
            )
            ->query(function (IzinCutiApprove $query) {
                return $query->where(function ($query) {
                    $query->whereHas('userCuti', function ($query) {
                        $query->where('company_id', Auth::user()->company_id);
                    });
                });
            })

            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('izinCutiApproveDua.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('izinCutiApproveDua.izinCutiApproveTiga.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional($record)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional(optional($record)->izinCutiApproveDua)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.izinCutiApproveTiga.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional(optional(optional($record)->izinCutiApproveDua)->izinCutiApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional($record)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional(optional($record)->izinCutiApproveDua)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.izinCutiApproveTiga.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional(optional(optional($record)->izinCutiApproveDua)->izinCutiApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn(IzinCutiApprove $record) =>
                                optional($record)->status === 2 ||
                                    optional(optional($record)->izinCutiApproveDua)->status === 2 ||
                                    optional(optional(optional($record)->izinCutiApproveDua)->izinCutiApproveTiga)->status === 2
                            ),
                        Section::make()
                            ->schema([
                                TextEntry::make('pilihan_cuti')
                                    ->badge()
                                    ->color('info')
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn(IzinCutiApprove $record) => optional($record)->keterangan_cuti === 'Cuti Khusus'),
                        Fieldset::make('Tanggal')
                            ->schema([
                                TextEntry::make('mulai_cuti')
                                    ->date(),
                                TextEntry::make('sampai_cuti')
                                    ->date(),
                                TextEntry::make('lama_cuti')
                                    ->badge(),
                            ])
                            ->columns(3),
                        Fieldset::make('Keterangan Cuti')
                            ->schema([
                                TextEntry::make('pesan_cuti')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                    ]),
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
            'index' => Pages\ListIzinCutiApproves::route('/'),
            'create' => Pages\CreateIzinCutiApprove::route('/create'),
            'edit' => Pages\EditIzinCutiApprove::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        $count = $modelClass::where('status', 0)
            ->where(function (Builder $query) {
                $query->whereHas('cutiKhusus.user', function (Builder $query) {
                    $query->where('company_id', Auth::user()->company_id);
                })
                    ->orWhereHas('cutiPribadi.user', function (Builder $query) {
                        $query->where('company_id', Auth::user()->company_id);
                    });
            })
            ->count();

        return (string) $count;
    }
}
