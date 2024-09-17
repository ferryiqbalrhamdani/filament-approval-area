<?php

namespace App\Filament\Clusters\IzinCuti\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CutiPribadi;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Clusters\IzinCuti;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\RelationManagers;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Widgets\CutiPribadiStats;

class CutiPribadiResource extends Resource
{
    protected static ?string $model = CutiPribadi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = IzinCuti::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('mulai_cuti')
                            ->required()
                            ->reactive(),
                        Forms\Components\DatePicker::make('sampai_cuti')
                            ->required()
                            ->afterOrEqual('mulai_cuti'),
                        Forms\Components\Textarea::make('keterangan_cuti')
                            ->required()
                            ->rows(7)
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mulai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sampai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lama_cuti')
                    ->searchable(),
                ViewColumn::make('izinCutiApprove.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinCutiApprove.izinCutiApproveDua.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinCutiApprove.izinCutiApproveDua.izinCutiApproveTiga.status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Tiga')
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
            ->filters([
                Tables\Filters\Filter::make('mulai_cuti')
                    ->form([
                        Forms\Components\DatePicker::make('cuti_dari')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_cuti')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['cuti_dari'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('mulai_cuti', '>=', $date),
                            )
                            ->when(
                                $data['sampai_cuti'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('mulai_cuti', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['cuti_dari'] ?? null) {
                            $indicators['cuti_dari'] = 'Tanggal Mulai: ' . Carbon::parse($data['cuti_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_cuti'] ?? null) {
                            $indicators['sampai_cuti'] = 'Tanggal Akhir: ' . Carbon::parse($data['sampai_cuti'])->toFormattedDateString();
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
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),
                    Tables\Actions\EditAction::make()
                        ->action(fn($record) => $record->izinCutiApprove->status == 0)
                        ->visible(fn($record) => $record->izinCutiApprove->status == 0),
                    Tables\Actions\DeleteAction::make()
                        ->action(function ($record) {
                            $lamaCuti = explode(' ', $record->lama_cuti);
                            $cutiUser = $record->user->cuti;

                            $record->user->update([
                                'cuti' => $cutiUser + (int)$lamaCuti[0],
                            ]);

                            $record->delete();

                            Notification::make()
                                ->title('Data berhasil di hapus')
                                ->success()
                                ->send();
                        })
                        ->visible(
                            fn($record) =>
                            $record->izinCutiApprove->status == 0 &&
                                $record->izinCutiApprove->izinCutiApproveDua->status == 0 &&
                                $record->izinCutiApprove->izinCutiApproveDua->izinCutiApproveTiga->status == 0
                        ),
                ])
                    ->link()
                    ->label('Actions'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(CutiPribadi $record): int =>
                $record->izinCutiApprove->status == 0 &&
                    $record->izinCutiApprove->izinCutiApproveDua->status == 0 &&
                    $record->izinCutiApprove->izinCutiApproveDua->izinCutiApproveTiga->status == 0,
            )
            ->query(
                fn(CutiPribadi $query) => $query->where('user_id', Auth::id())
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
                                ViewEntry::make('izinCutiApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status-surat-izin'),
                                ViewEntry::make('izinCutiApprove.izinCutiApproveDua.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Dua'),
                                ViewEntry::make('izinCutiApprove.izinCutiApproveDua.izinCutiApproveTiga.status')
                                    ->view('infolists.components.status-surat-izin')
                                    ->label('Status Tiga'),
                            ])->columns(3),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('izinCutiApprove.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(CutiPribadi $record) => optional($record->izinCutiApprove)->status === 2),
                                        TextEntry::make('izinCutiApprove.izinCutiApproveDua.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(CutiPribadi $record) => optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->status === 2),
                                        TextEntry::make('izinCutiApprove.izinCutiApproveDua.izinCutiApproveTiga.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(CutiPribadi $record) => optional(optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->izinCutiApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('izinCutiApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(CutiPribadi $record) => optional($record->izinCutiApprove)->status === 2),
                                        TextEntry::make('izinCutiApprove.izinCutiApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(CutiPribadi $record) => optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->status === 2),
                                        TextEntry::make('izinCutiApprove.izinCutiApproveDua.izinCutiApproveTiga.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(CutiPribadi $record) => optional(optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->izinCutiApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn(CutiPribadi $record) =>
                                optional($record->izinCutiApprove)->status === 2 ||
                                    optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->status === 2 ||
                                    optional(optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->izinCutiApproveTiga)->status === 2
                            ),

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
                                TextEntry::make('keterangan_cuti')
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

    public static function getWidgets(): array
    {
        return [
            CutiPribadiStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCutiPribadis::route('/'),
            'create' => Pages\CreateCutiPribadi::route('/create'),
            'edit' => Pages\EditCutiPribadi::route('/{record}/edit'),
        ];
    }
}
