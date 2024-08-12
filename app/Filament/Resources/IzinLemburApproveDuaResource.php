<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IzinLemburApproveDuaResource\Pages;
use App\Filament\Resources\IzinLemburApproveDuaResource\RelationManagers;
use App\Models\IzinLemburApproveDua;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class IzinLemburApproveDuaResource extends Resource
{
    protected static ?string $model = IzinLemburApproveDua::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Dua';

    protected static ?int $navigationSort = 22;

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
                Tables\Columns\TextColumn::make('izinLemburApprove.izinLembur.user.first_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApprove.izinLembur.tanggal_lembur')
                    ->label('Tanggal Lembur')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApprove.izinLembur.start_time')
                    ->label('Start Time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApprove.izinLembur.end_time')
                    ->label('End Time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLemburApprove.izinLembur.lama_lembur')
                    ->label('Lama Lembur')
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
                ViewColumn::make('status')
                    ->view('tables.columns.status-surat-izin')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                // ViewColumn::make('status')
                //     ->view('tables.columns.status-surat-izin')
                //     ->label('Status Tiga')
                //     ->alignment(Alignment::Center)
                //     ->sortable()
                //     ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (IzinLemburApproveDua $record, array $data): void {
                            // Hapus data di izinLemburApproveDua jika ada dan statusnya 0
                            // $record->izinLemburApproveDua()
                            //     ->where('status', 0)
                            //     ->delete();

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
                        ->action(function (IzinLemburApproveDua $record, array $data): void {
                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);

                            // $record->izinLemburApproveDua()->create();


                            // $record->izinLemburApproveDua()->create([
                            //     'surat_izin_approve_id' => $record->id,
                            // ]);

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
                        ->action(function (IzinLemburApproveDua $record, array $data): void {
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
            ->checkIfRecordIsSelectableUsing(
                fn(IzinLemburApproveDua $record): int => $record->status === 0,
            )
            ->query(function (IzinLemburApproveDua $query) {
                return $query->whereHas('izinLemburApprove.izinLembur.user', function ($query) {
                    $query->where('company_id', Auth::user()->company_id);
                });
            })
            ->defaultSort('created_at', 'desc')
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
                                // $record->izinLemburApproveDua()->create();
                            }



                            Notification::make()
                                ->title('Data yang dipilih berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
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
            'index' => Pages\ListIzinLemburApproveDuas::route('/'),
            'create' => Pages\CreateIzinLemburApproveDua::route('/create'),
            'edit' => Pages\EditIzinLemburApproveDua::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        $count = $modelClass::where('status', 0)
            ->whereHas('izinLemburApprove.izinLembur.user', function (Builder $query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->count();

        return (string) $count;
    }
}
