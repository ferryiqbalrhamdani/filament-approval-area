<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\IzinLembur;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IzinLemburResource\Pages;
use App\Filament\Resources\IzinLemburResource\RelationManagers;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;

class IzinLemburResource extends Resource
{
    protected static ?string $model = IzinLembur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'User';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_lembur')
                            ->default(Carbon::now()->format('Y-m-d'))
                            ->required(),
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Jam mulai')
                            ->default('17:00')
                            ->seconds(false)
                            ->required()
                            ->minutesStep(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->label('Jam selesai')
                            ->default('18:00')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\Textarea::make('keterangan_lembur')
                            ->columnSpanFull()
                            ->required()
                            ->rows(5),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_lembur')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lama_lembur')
                    ->badge()
                    ->suffix(' Jam')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLemburApprove.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLemburApprove.izinLemburApproveDua.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLemburApprove.izinLemburApproveDua.izinLemburApproveTiga.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
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
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->filters([
                Tables\Filters\Filter::make('tanggal_lembur')
                    ->form([
                        Forms\Components\DatePicker::make('lembur_dari')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_lembur')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['lembur_dari'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_lembur', '>=', $date),
                            )
                            ->when(
                                $data['sampai_lembur'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_lembur', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['lembur_dari'] ?? null) {
                            $indicators['lembur_dari'] = 'Tanggal Mulai: ' . Carbon::parse($data['lembur_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_lembur'] ?? null) {
                            $indicators['sampai_lembur'] = 'Tanggal Akhir: ' . Carbon::parse($data['sampai_lembur'])->toFormattedDateString();
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
                            $query->whereYear('tanggal_lembur', Carbon::now()->year);
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
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),
                    Tables\Actions\EditAction::make()
                        ->action(fn($record) => $record->IzinLemburApprove->status == 0)
                        ->visible(fn($record) => $record->IzinLemburApprove->status == 0),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => $record->IzinLemburApprove->status == 0),
                ])
                    ->link()
                    ->label('Actions'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(IzinLembur $record): int => $record->izinLemburApprove->status === 0,
            )
            ->query(
                fn(IzinLembur $query) => $query->where('user_id', Auth::id())
            );
    }

    protected function getTableQuery(): Builder
    {
        return IzinLembur::where('user_id', Auth::id())->latest();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('izinLemburApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('izinLemburApprove.izinLemburApproveDua.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('izinLemburApprove.izinLemburApproveDua.izinLemburApproveTiga.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('izinLemburApprove.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinLembur $record) => optional($record->izinLemburApprove)->status === 2),
                                        TextEntry::make('izinLemburApprove.izinLemburApproveDua.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinLembur $record) => optional(optional($record->izinLemburApprove)->izinLemburApproveDua)->status === 2),
                                        TextEntry::make('izinLemburApprove.izinLemburApproveDua.izinLemburApproveTiga.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinLembur $record) => optional(optional(optional($record->izinLemburApprove)->izinLemburApproveDua)->izinLemburApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(1),
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('izinLemburApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinLembur $record) => optional($record->izinLemburApprove)->status === 2),
                                        TextEntry::make('izinLemburApprove.izinLemburApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinLembur $record) => optional(optional($record->izinLemburApprove)->izinLemburApproveDua)->status === 2),
                                        TextEntry::make('izinLemburApprove.izinLemburApproveDua.izinLemburApproveTiga.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinLembur $record) => optional(optional(optional($record->izinLemburApprove)->izinLemburApproveDua)->izinLemburApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(3),
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn(IzinLembur $record) =>
                                optional($record->izinLemburApprove)->status === 2 ||
                                    optional(optional($record->izinLemburApprove)->izinLemburApproveDua)->status === 2 ||
                                    optional(optional(optional($record->izinLemburApprove)->izinLemburApproveDua)->izinLemburApproveTiga)->status === 2
                            ),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('tanggal_lembur')
                                    ->date(),
                                TextEntry::make('start_time')
                                    ->time('H:i'),
                                TextEntry::make('end_time')
                                    ->time('H:i'),
                                TextEntry::make('lama_lembur')
                                    ->suffix(' Jam')
                                    ->badge(),
                            ])
                            ->columns(4),

                        Fieldset::make('Keterangan Izin')
                            ->schema([
                                TextEntry::make('keterangan_lembur')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                    ])
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
            'index' => Pages\ListIzinLemburs::route('/'),
            'create' => Pages\CreateIzinLembur::route('/create'),
            'edit' => Pages\EditIzinLembur::route('/{record}/edit'),
        ];
    }
}
