<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\SuratIzin;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\SuratIzinResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use App\Filament\Resources\SuratIzinResource\RelationManagers;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;

class SuratIzinResource extends Resource
{
    protected static ?string $model = SuratIzin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'User';

    protected static ?int $navigationSort = 0;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('keperluan_izin')
                                    ->options([
                                        'Izin Datang Terlambat' => 'Izin Datang Terlambat',
                                        'Izin Tidak Masuk Kerja' => 'Izin Tidak Masuk Kerja',
                                        'Izin Meninggalkan Kantor' => 'Izin Meninggalkan Kantor',
                                        'Tugas Meninggalkan Kantor' => 'Tugas Meninggalkan Kantor',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->reactive(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Fieldset::make('Tanggal')
                                    ->schema([
                                        Forms\Components\Section::make()
                                            ->schema([
                                                Forms\Components\Radio::make('status_izin')
                                                    ->label('Lama Izin')
                                                    ->options([
                                                        'sehari' => 'Sehari',
                                                        'lebih_dari_sehari' => 'Lebih Dari Sehari',
                                                    ])
                                                    ->default('sehari')
                                                    ->required()
                                                    ->columns(3)
                                                    ->reactive()
                                                    ->columnSpanFull()
                                                    ->visible(fn(Get $get) => $get('keperluan_izin') === 'Tugas Meninggalkan Kantor'),
                                            ])
                                            ->visible(fn(Get $get) => $get('keperluan_izin') === 'Tugas Meninggalkan Kantor'),
                                        Forms\Components\DatePicker::make('tanggal_izin')
                                            ->required()
                                            ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                                                'Izin Datang Terlambat',
                                                'Izin Tidak Masuk Kerja',
                                                'Izin Meninggalkan Kantor',
                                                'Tugas Meninggalkan Kantor',
                                            ])),
                                        Forms\Components\DatePicker::make('sampai_tanggal')
                                            ->required()
                                            ->afterOrEqual('tanggal_izin')
                                            ->visible(fn(Get $get) => $get('keperluan_izin') === 'Izin Tidak Masuk Kerja'  || $get('status_izin') === 'lebih_dari_sehari'),
                                    ])->columns(2),
                            ])
                            ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                                'Izin Datang Terlambat',
                                'Izin Tidak Masuk Kerja',
                                'Izin Meninggalkan Kantor',
                                'Tugas Meninggalkan Kantor',
                            ])),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Fieldset::make('Lama Izin')
                                    ->schema([
                                        Forms\Components\TimePicker::make('jam_izin')
                                            ->seconds(false)
                                            ->timezone('Asia/Jakarta')
                                            ->required()
                                            ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                                                'Izin Meninggalkan Kantor',
                                                'Tugas Meninggalkan Kantor',
                                            ])),
                                        Forms\Components\TimePicker::make('sampai_jam')
                                            ->seconds(false)
                                            ->timezone('Asia/Jakarta')
                                            ->required()
                                            ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                                                'Izin Datang Terlambat',
                                                'Izin Meninggalkan Kantor',
                                                'Tugas Meninggalkan Kantor',
                                            ])),
                                    ])->columns(2),
                            ])
                            ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                                'Izin Datang Terlambat',
                                'Izin Meninggalkan Kantor',
                                'Tugas Meninggalkan Kantor',
                            ]))
                            ->hidden(fn(Get $get) => $get('status_izin') === 'lebih_dari_sehari' && $get('keperluan_izin') === 'Tugas Meninggalkan Kantor'),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Textarea::make('keterangan_izin')
                                    ->required()
                                    ->rows(5)
                                    ->columnSpanFull()
                                    ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                                        'Izin Datang Terlambat',
                                        'Izin Tidak Masuk Kerja',
                                        'Izin Meninggalkan Kantor',
                                        'Tugas Meninggalkan Kantor',
                                    ])),
                            ])
                            ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                                'Izin Datang Terlambat',
                                'Izin Tidak Masuk Kerja',
                                'Izin Meninggalkan Kantor',
                                'Tugas Meninggalkan Kantor',
                            ])),
                    ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Bukti Foto')
                            ->schema([
                                Forms\Components\FileUpload::make('photo')
                                    ->image()
                                    ->hiddenLabel()
                                    ->helperText('Format foto: jpg, png, jpeg'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->visible(fn(Get $get) => in_array($get('keperluan_izin'), [
                        'Izin Datang Terlambat',
                        'Izin Tidak Masuk Kerja',
                        'Izin Meninggalkan Kantor',
                        'Tugas Meninggalkan Kantor',
                    ])),
            ])
            ->columns(3);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('keperluan_izin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lama_izin')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_izin')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sampai_tanggal')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('durasi_izin')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jam_izin')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('sampai_jam')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),
                ViewColumn::make('suratIzinApprove.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('suratIzinApprove.suratIzinApproveDua.status')
                    ->label('Status Dua')
                    ->view('tables.columns.status-surat-izin')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('suratIzinApprove.suratIzinApproveDua.suratIzinApproveTiga.status')
                    ->label('Status Tiga')
                    ->view('tables.columns.status-surat-izin')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('tanggal_izin')
                    ->form([
                        Forms\Components\DatePicker::make('izin_dari')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_izin')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['izin_dari'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_izin', '>=', $date),
                            )
                            ->when(
                                $data['sampai_izin'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_izin', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['izin_dari'] ?? null) {
                            $indicators['izin_dari'] = 'Tanggal Mulai: ' . Carbon::parse($data['izin_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_izin'] ?? null) {
                            $indicators['sampai_izin'] = 'Tanggal Akhir: ' . Carbon::parse($data['sampai_izin'])->toFormattedDateString();
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
                            $query->whereYear('tanggal_izin', Carbon::now()->year);
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
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),
                    Tables\Actions\EditAction::make()
                        ->action(fn($record) => $record->suratIzinApprove->status == 0)
                        ->visible(fn($record) => $record->suratIzinApprove->status == 0),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => $record->suratIzinApprove->status == 0),
                ])
                    ->link()
                    ->label('Actions'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(SuratIzin $record): int => $record->suratIzinApprove->status === 0,
            )
            ->query(
                fn(SuratIzin $query) => $query->where('user_id', Auth::id())
            );
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('suratIzinApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('suratIzinApprove.suratIzinApproveDua.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('suratIzinApprove.suratIzinApproveDua.suratIzinApproveTiga.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('suratIzinApprove.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(SuratIzin $record) => optional($record->suratIzinApprove)->status === 2),
                                        TextEntry::make('suratIzinApprove.suratIzinApproveDua.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(SuratIzin $record) => optional(optional($record->suratIzinApprove)->suratIzinApproveDua)->status === 2),
                                        TextEntry::make('suratIzinApprove.suratIzinApproveDua.suratIzinApproveTiga.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(SuratIzin $record) => optional(optional(optional($record->suratIzinApprove)->suratIzinApproveDua)->suratIzinApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('suratIzinApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(SuratIzin $record) => optional($record->suratIzinApprove)->status === 2),
                                        TextEntry::make('suratIzinApprove.suratIzinApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(SuratIzin $record) => optional(optional($record->suratIzinApprove)->suratIzinApproveDua)->status === 2),
                                        TextEntry::make('suratIzinApprove.suratIzinApproveDua.suratIzinApproveTiga.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(SuratIzin $record) => optional(optional(optional($record->suratIzinApprove)->suratIzinApproveDua)->suratIzinApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn(SuratIzin $record) =>
                                optional($record->suratIzinApprove)->status === 2 ||
                                    optional(optional($record->suratIzinApprove)->suratIzinApproveDua)->status === 2 ||
                                    optional(optional(optional($record->suratIzinApprove)->suratIzinApproveDua)->suratIzinApproveTiga)->status === 2
                            ),
                        Section::make()
                            ->schema([
                                TextEntry::make('keperluan_izin')
                                    ->badge()
                                    ->color('info')
                                    ->columnSpanFull(),
                            ]),

                        Fieldset::make('Tanggal')
                            ->schema([
                                TextEntry::make('lama_izin')
                                    ->badge(),
                                TextEntry::make('tanggal_izin')
                                    ->date(),
                                TextEntry::make('sampai_tanggal')
                                    ->date(),
                            ])
                            ->columns(3),

                        Fieldset::make('Lama Izin')
                            ->schema([
                                TextEntry::make('durasi_izin')
                                    ->label('Durasi'),
                                TextEntry::make('jam_izin')
                                    ->time('H:i'),
                                TextEntry::make('sampai_jam')
                                    ->time('H:i'),
                            ])
                            ->columns(3)
                            ->visible(fn(SuratIzin $record): string => $record->lama_izin === '1 Hari' && $record->durasi_izin),
                        Fieldset::make('Keterangan Izin')
                            ->schema([
                                TextEntry::make('keterangan_izin')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                        Fieldset::make('Bukti Foto')
                            ->schema([
                                ImageEntry::make('photo')
                                    ->hiddenlabel()
                                    ->width(800)
                                    ->height(800)
                                    ->size(800)
                                    ->columnSpanFull(),
                            ])->visible(fn(SuratIzin $record): string => $record->photo !== null),
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
            'index' => Pages\ListSuratIzins::route('/'),
            'create' => Pages\CreateSuratIzin::route('/create'),
            'edit' => Pages\EditSuratIzin::route('/{record}/edit'),
        ];
    }
}
