<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Office;
use App\Models\Company;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Enum\GenderType;
use App\Models\Division;
use App\Models\Position;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static ?int $navigationSort = 41;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->placeholder('Jhon')
                            ->inlineLabel()
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateHydrated(fn($set, $get) => self::generateUsername($set, $get))
                            ->afterStateUpdated(fn($set, $get) => self::generateUsername($set, $get)),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->placeholder('Albert Doe')
                            ->inlineLabel()
                            ->helperText('optional')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->inlineLabel()
                            ->helperText('Username akan otomatis dibuat')
                            ->unique(User::class, 'username', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->inlineLabel()
                            ->default('password')
                            ->required()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('By default password is "password"')
                            ->visibleOn('create'),
                        Forms\Components\Radio::make('jk')
                            ->label('Jenis Kelamin')
                            ->inlineLabel()
                            ->inline()
                            ->options([
                                GenderType::L->value => 'Laki-laki',
                                GenderType::P->value => 'Perempuan',
                            ])
                            ->default('Laki-laki')
                            ->required()
                    ])->columns(2),
                Forms\Components\Section::make('Informasi Tempat Kerja')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Perusahaan')
                            ->inlineLabel()
                            ->required()
                            ->options(Company::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('office_id')
                            ->label('Kantor')
                            ->inlineLabel()
                            ->required()
                            ->options(Office::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('position_id')
                            ->label('Posisi')
                            ->inlineLabel()
                            ->required()
                            ->options(Position::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                $set('slug', Position::generateUniqueSlug($state));
                                            })
                                            ->required()
                                            ->live(onBlur: true)
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->readOnly()
                                            ->afterStateUpdated(function (Closure $set, $state) {
                                                $set('slug', Position::generateUniqueSlug($state));
                                            })
                                            ->maxLength(255),
                                        Forms\Components\Toggle::make('is_active')
                                            ->default(true)
                                            ->required(),
                                    ])->columns(2),
                            ])
                            ->createOptionUsing(function ($data) {
                                return Position::create([
                                    'name' => $data['name'],
                                    'slug' => $data['slug'],
                                    'is_active' => $data['is_active'],
                                ]);
                            }),
                        Forms\Components\Select::make('division_id')
                            ->label('Divisi')
                            ->inlineLabel()
                            ->required()
                            ->options(Division::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                $set('slug', Division::generateUniqueSlug($state));
                                            })
                                            ->required()
                                            ->live(onBlur: true)
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->readOnly()
                                            ->afterStateUpdated(function (Closure $set, $state) {
                                                $set('slug', Division::generateUniqueSlug($state));
                                            })
                                            ->maxLength(255),
                                        Forms\Components\Toggle::make('is_active')
                                            ->default(true)
                                            ->required(),
                                    ])->columns(2),
                            ])
                            ->createOptionUsing(function ($data) {
                                return Position::create([
                                    'name' => $data['name'],
                                    'slug' => $data['slug'],
                                    'is_active' => $data['is_active'],
                                ]);
                            }),
                        Forms\Components\Select::make('status_karyawan')
                            ->required()
                            ->inlineLabel()
                            ->options([
                                'tetap' => 'Tetap',
                                'kontrak' => 'Kontrak',
                                'magang' => 'Magang',
                                'harian lepas' => 'Harian Lepas',
                            ])
                            ->searchable()
                            ->reactive(),
                        Forms\Components\TextInput::make('cuti')
                            ->label('Sisa Cuti')
                            ->inlineLabel()
                            ->integer()
                            ->default(0)
                            ->maxLength(255)
                            ->minValue(0)
                            ->required()
                            ->visibleOn('edit'),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name', fn(Builder $query) => $query->where('id', '>', 1)->orWhere('name', '!=', 'super_admin')->orderBy('name', 'asc'))
                            ->required()
                            ->inlineLabel()
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Forms\Components\DatePicker::make('tgl_pengangkatan')
                            ->inlineLabel()
                            ->visible(fn(Get $get) => $get('status_karyawan') === 'tetap'),
                    ])->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Skema Approve')
                            ->schema([
                                Forms\Components\Select::make('user_approve_id')
                                    ->label('User Approve Satu')
                                    ->relationship(
                                        name: 'userApprove',
                                        modifyQueryUsing: fn(Builder $query) => $query->orderBy('first_name')->orderBy('last_name'),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable(['first_name', 'last_name'])
                                    ->helperText('Jika tidak ada user approve satu, biarkan kosong')
                                    ->preload(),
                                Forms\Components\Select::make('user_approve_dua_id')
                                    ->label('User Approve Dua')
                                    ->relationship(
                                        name: 'userApproveDua',
                                        modifyQueryUsing: fn(Builder $query) => $query->orderBy('first_name')->orderBy('last_name'),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable(['first_name', 'last_name'])
                                    ->helperText('Jika tidak ada user approve dua, biarkan kosong')
                                    ->preload(),

                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    protected static function generateUsername($set, $get)
    {
        $firstName = $get('first_name');

        if ($get('username') && $firstName) {
            $set('username', $get('username'));
        } else {
            if ($firstName) {
                // Menghapus spasi dari first_name
                $baseUsername = strtolower(Str::slug(str_replace(' ', '', $firstName)));
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
                Tables\Columns\TextColumn::make('office.name')
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
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->color('gray')
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\Action::make('resetPassword')
                //     ->icon('heroicon-o-arrow-path')
                //     ->color('primary')
                //     ->action(function (User $record, array $data): void {
                //         $record->update([
                //             'password' => Hash::make('password'),
                //         ]);
                //         Notification::make()
                //             ->title('User Password Berhasil Diubah')
                //             ->success()
                //             ->send();
                //     })
                //     ->requiresConfirmation()
                //     ->visible(fn ($record) => $record->id != 1),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->visible(function (User $record) {
                            return $record->id !== 1 && !$record->hasRole('super_admin');
                        }),
                ])
                    ->link()
                    ->label('Actions'),
            ], position: ActionsPosition::BeforeCells)
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

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Product $record */

        return [
            'Company' => optional($record->company)->name,
        ];
    }
}
