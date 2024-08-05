<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Office;
use App\Models\Company;
use App\Enum\GenderType;
use App\Models\Division;
use App\Models\Position;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Biodata')
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('First Name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->maxLength(255)
                                    ->afterStateHydrated(fn ($set, $get) => self::generateUsername($set, $get))
                                    ->afterStateUpdated(fn ($set, $get) => self::generateUsername($set, $get)),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Last Name')
                                    ->helperText('optional')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->helperText('Username akan otomatis dibuat')
                                    ->unique(User::class, 'username', ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->default('password')
                                    ->required()
                                    ->revealable()
                                    ->maxLength(255)
                                    ->helperText('By default password is "password"')
                                    ->visibleOn('create'),
                                Forms\Components\Radio::make('jk')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        GenderType::L->value => 'Laki-laki',
                                        GenderType::P->value => 'Perempuan',
                                    ])
                                    ->default('Laki-laki')
                                    ->required()
                                    ->columns(3)
                            ])->columns(3),
                        Forms\Components\Fieldset::make('Informasi Tempat Kerja')
                            ->schema([
                                Forms\Components\Select::make('company_id')
                                    ->label('Company')
                                    ->required()
                                    ->options(Company::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                    ->searchable(),
                                Forms\Components\Select::make('office_id')
                                    ->label('Office')
                                    ->required()
                                    ->options(Office::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                    ->searchable(),
                                Forms\Components\Select::make('position_id')
                                    ->label('Position')
                                    ->required()
                                    ->options(Position::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                    ->searchable(),
                                Forms\Components\Select::make('division_id')
                                    ->label('Division')
                                    ->required()
                                    ->options(Division::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                    ->searchable(),
                                Forms\Components\Select::make('status_karyawan')
                                    ->required()
                                    ->options([
                                        'tetap' => 'Tetap',
                                        'kontrak' => 'Kontrak',
                                        'magang' => 'Magang',
                                    ])
                                    ->searchable(),
                                Forms\Components\Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->required()
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                            ])->columns(3),
                    ]),
            ]);
    }

    protected static function generateUsername($set, $get)
    {
        $firstName = $get('first_name');

        if ($get('username')) {
            $set('username', $get('username'));
        } else {
            if ($firstName) {
                $baseUsername = strtolower(Str::slug($firstName));
                $username = $baseUsername;
                $count = 1;

                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $count;
                    $count++;
                }

                $set('username', $username);
            } else {
                $set('username', null);
            }
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Full Name')
                    ->formatStateUsing(function ($record) {
                        return $record->first_name . ' ' . $record->last_name;
                    })
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ofiice.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('jk')
                    ->label('JK')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_karyawan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
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
                Tables\Actions\Action::make('resetPassword')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => Hash::make('password'),
                        ]);
                        Notification::make()
                            ->title('User Password Berhasil Diubah')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->id != 1),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
