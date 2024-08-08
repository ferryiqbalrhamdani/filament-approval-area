<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\SuratIzinApproveDua;
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
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SuratIzinApproveDuaResource\Pages;
use App\Filament\Resources\SuratIzinApproveDuaResource\RelationManagers;

class SuratIzinApproveDuaResource extends Resource
{
    protected static ?string $model = SuratIzinApproveDua::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Dua';

    protected static ?int $navigationSort = 20;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('surat_izin_id')
                    ->numeric(),
                Forms\Components\TextInput::make('surat_izin_approve_id')
                    ->numeric(),
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.user.first_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.keperluan_izin')
                    ->label('Keperluan Izin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.lama_izin')
                    ->label('Lama Izin')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.tanggal_izin')
                    ->label('Tgl. Izin')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.sampai_tanggal')
                    ->label('Sampai Tgl. Izin')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.durasi_izin')
                    ->label('Durasi Izin')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.jam_izin')
                    ->label('Jam Izin')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('suratIzinApprove.suratIzin.sampai_jam')
                    ->label('Sampai Jam')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),
                ViewColumn::make('suratIzinApprove.status')
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
                ViewColumn::make('suratIzinApproveTiga.status')
                    ->label('Status Tiga')
                    ->view('tables.columns.status-surat-izin')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter berdasarkan rentang tanggal
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
                                $query->whereHas('suratIzinApprove.suratIzin', function ($query) use ($start) {
                                    $query->whereDate('tanggal_izin', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('suratIzinApprove.suratIzin', function ($query) use ($end) {
                                    $query->whereDate('tanggal_izin', '<=', $end);
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
                            $query->whereHas('suratIzinApprove.suratIzin', function ($query) use ($data) {
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
                        ->action(function (SuratIzinApproveDua $record, array $data): void {
                            // Hapus data di SuratIzinApproveDua jika ada dan statusnya 0
                            $record->suratIzinApproveTiga()
                                ->where('status', 0)
                                ->delete();

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
                        ->visible(fn ($record) => $record->status > 0 && $record->suratIzinApproveTiga && $record->suratIzinApproveTiga->status === 0),
                    Tables\Actions\Action::make('Approve')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->action(function (SuratIzinApproveDua $record, array $data): void {
                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);

                            $record->suratIzinApproveTiga()->create([
                                'surat_izin_approve_dua_id' => $record->id,
                            ]);

                            Notification::make()
                                ->title('Data berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->color('success')
                        ->hidden(fn ($record) => $record->status > 0),
                    Tables\Actions\Action::make('Reject')
                        ->form([
                            Forms\Components\TextArea::make('keterangan')
                                // ->hiddenLabel()
                                ->required()
                                ->maxLength(255),
                        ])
                        ->requiresConfirmation()
                        ->icon('heroicon-o-x-circle')
                        ->action(function (SuratIzinApproveDua $record, array $data): void {
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
                        ->hidden(fn ($record) => $record->status > 0),
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
                                $record->suratIzinApproveTiga()->create();
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
                fn (SuratIzinApproveDua $record): int => $record->status === 0,
            )
            ->query(function (SuratIzinApproveDua $query) {
                return $query->whereHas('suratIzinApprove.suratIzin.user', function ($query) {
                    $query->where('company_id', Auth::user()->company_id);
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
                        Fieldset::make('Informasi User')
                            ->schema([
                                TextEntry::make('suratIzinApprove.suratIzin.user.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('suratIzinApprove.suratIzin.user.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('suratIzinApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('suratIzinApproveTiga.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Fieldset::make('Keterangan')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->hiddenlabel(),
                            ])->visible(fn ($record) => $record->keterangan),
                    ]),
                Section::make()
                    ->schema([
                        TextEntry::make('suratIzinApprove.suratIzin.keperluan_izin')
                            ->label('Keperluan Izin')
                            ->badge()
                            ->color('info')
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Tanggal')
                    ->schema([
                        TextEntry::make('suratIzinApprove.suratIzin.lama_izin')
                            ->label('Lama Izin')
                            ->badge(),
                        TextEntry::make('suratIzinApprove.suratIzin.tanggal_izin')
                            ->label('Tgl. Izin')
                            ->date(),
                        TextEntry::make('suratIzinApprove.suratIzin.sampai_tanggal')
                            ->label('Sampai Tgl. Izin')
                            ->date(),
                    ])
                    ->columns(3),

                Fieldset::make('Lama Izin')
                    ->schema([
                        TextEntry::make('suratIzinApprove.suratIzin.durasi_izin')
                            ->label('Durasi')
                            ->label('Durasi'),
                        TextEntry::make('suratIzinApprove.suratIzin.jam_izin')
                            ->label('Jam Izin')
                            ->time('H:i'),
                        TextEntry::make('suratIzinApprove.suratIzin.sampai_jam')
                            ->label('Sampai Jam')
                            ->time('H:i'),
                    ])
                    ->columns(3)
                    ->visible(fn (SuratIzinApproveDua $record): string => $record->suratIzinApprove->suratIzin->lama_izin === '1 Hari' && $record->suratIzinApprove->suratIzin->durasi_izin),

                Fieldset::make('Keterangan Izin')
                    ->schema([
                        TextEntry::make('suratIzinApprove.suratIzin.keterangan_izin')
                            ->hiddenlabel()
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Bukti Foto')
                    ->schema([
                        ImageEntry::make('suratIzinApprove.suratIzin.photo')
                            ->hiddenlabel()
                            ->width(800)
                            ->height(800)
                            ->size(800)
                            ->columnSpanFull(),
                    ])->visible(fn (SuratIzinApproveDua $record): string => $record->suratIzinApprove->suratIzin->photo !== null),
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
            'index' => Pages\ListSuratIzinApproveDuas::route('/'),
            'create' => Pages\CreateSuratIzinApproveDua::route('/create'),
            'edit' => Pages\EditSuratIzinApproveDua::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        $count = $modelClass::where('status', 0)
            ->whereHas('suratIzinApprove.suratIzin.user', function (Builder $query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->count();

        return (string) $count;
    }
}