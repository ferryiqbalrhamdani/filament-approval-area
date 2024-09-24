<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PasswordReset;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PasswordResetResource\Pages;
use App\Filament\Resources\PasswordResetResource\RelationManagers;

class PasswordResetResource extends Resource
{
    protected static ?string $model = PasswordReset::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Settings';


    protected static ?int $navigationSort = 43;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Full Name')
                    ->formatStateUsing(function ($record) {
                        return $record->user->first_name . ' ' . $record->user->last_name;
                    })
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.company.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.office.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.position.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.division.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.jk')
                    ->label('JK')
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Reset Password')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function (PasswordReset $record, array $data): void {

                        $record->user->update([
                            'password' => Hash::make('password'),
                        ]);

                        $record->delete();

                        Notification::make()
                            ->title('Password user berhasil direset')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Reset Password')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->user->update([
                                    'password' => Hash::make('password'),
                                ]);

                                $record->delete();
                            }


                            Notification::make()
                                ->title('Password user berhasil direset')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->query(
                fn(PasswordReset $query) => $query->whereHas('user', function ($query) {
                    $query->where('company_id', Auth::user()->company_id);
                })
            );
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
            'index' => Pages\ListPasswordResets::route('/'),
            'create' => Pages\CreatePasswordReset::route('/create'),
            'edit' => Pages\EditPasswordReset::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::whereHas('user', function ($query) {
            $query->where('company_id', Auth::user()->company_id);
        })->count();
    }
}
