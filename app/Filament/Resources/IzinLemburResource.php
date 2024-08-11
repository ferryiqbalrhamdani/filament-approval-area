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
                        Forms\Components\DatePicker::make('tanggal_cuti')
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
                        Forms\Components\Textarea::make('keterangan')
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
                Tables\Columns\TextColumn::make('tanggal_cuti')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
