<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\SuratIzin;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SuratIzinResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use App\Filament\Resources\SuratIzinResource\RelationManagers;

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
                                    ->native(false),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Fieldset::make('Tanggal')
                                    ->schema([
                                        Forms\Components\DatePicker::make('tanggal_izin'),
                                        Forms\Components\DatePicker::make('sampai_tanggal'),
                                    ])->columns(2),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Fieldset::make('Lama Izin')
                                    ->schema([
                                        Forms\Components\TimePicker::make('jam_izin')
                                            ->seconds(false)
                                            ->timezone('Asia/Jakarta'),
                                        Forms\Components\TimePicker::make('sampai_jam')
                                            ->seconds(false)
                                            ->timezone('Asia/Jakarta'),
                                    ])->columns(2),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Textarea::make('keterangan_izin')
                                    ->required()
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Bukti Foto')
                            ->schema([
                                Forms\Components\FileUpload::make('photo')
                                    ->hiddenLabel(),
                            ]),
                    ])->columnSpan(['lg' => 1]),
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
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_izin')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['izin_dari'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_izin', '>=', $date),
                            )
                            ->when(
                                $data['sampai_izin'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_izin', '<=', $date),
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
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn ($record) => $record->suratIzinApprove->status == 0),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn ($record) => $record->suratIzinApprove->status == 0),
                ])
                    ->tooltip('Actions'),
            ])
            ->query(fn (SuratIzin $query) => $query->where('user_id', Auth::id()));
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
