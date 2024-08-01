<?php

namespace App\Filament\Clusters\Companies\Resources\CompanyResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Office;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OfficesRelationManager extends RelationManager
{
    protected static string $relationship = 'offices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name Office')
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('slug', Office::generateUniqueSlug($state));
                            })
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->readOnly()
                            ->afterStateUpdated(function (Closure $set, $state) {
                                $set('slug', Office::generateUniqueSlug($state));
                            })
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name Office')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
