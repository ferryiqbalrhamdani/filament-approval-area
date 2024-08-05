<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\SuratIzinApproveDua;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SuratIzinApproveDuaResource\Pages;
use App\Filament\Resources\SuratIzinApproveDuaResource\RelationManagers;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Support\Facades\Auth;

class SuratIzinApproveDuaResource extends Resource
{
    protected static ?string $model = SuratIzinApproveDua::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Dua';


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
                    ->toggleable(isToggledHiddenByDefault: true)
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
                ViewColumn::make('status')->view('tables.columns.status-surat-izin')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('status_tiga')->view('tables.columns.status-surat-izin')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
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
                        ->action(function (SuratIzinApproveDua $record, array $data): void {
                            // // Hapus data di SuratIzinApproveDua jika ada dan statusnya 0
                            // $record->suratIzinApproveDua()
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
                        }),
                    // ->visible(fn ($record) => $record->status > 0 && $record->suratIzinApproveDua && $record->suratIzinApproveDua->status === 0),
                    Tables\Actions\Action::make('Approve')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->action(function (SuratIzinApproveDua $record, array $data): void {
                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);

                            // $record->suratIzinApproveDua()->create([
                            //     'surat_izin_approve_id' => $record->id,
                            // ]);

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
                    ->tooltip('Actions'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (SuratIzinApproveDua $query) {
                return $query->whereHas('suratIzinApprove.suratIzin.user', function ($query) {
                    $query->where('company_id', Auth::user()->company_id);
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
            'index' => Pages\ListSuratIzinApproveDuas::route('/'),
            'create' => Pages\CreateSuratIzinApproveDua::route('/create'),
            'edit' => Pages\EditSuratIzinApproveDua::route('/{record}/edit'),
        ];
    }
}
