<?php

namespace App\Filament\Resources\Remont;

use App\Filament\Resources\Remont\BlanckResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Remont\BlanckResource\Pages;
use App\Models\Remont\Blanck;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlanckResource extends Resource
{
    protected static ?string $model = Blanck::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Ремонт';
    protected static ?string $modelLabel = 'Квитанція';
    protected static ?string $pluralModelLabel = 'Квитанції';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Номер квитанції')
                            ->placeholder('Автоматично, якщо порожньо'),
                        Forms\Components\DateTimePicker::make('data')
                            ->label('Дата')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(Blanck::STATUSES) // Переконайтеся, що константа в моделі називається STATUSES
                            ->default('1')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Дані клієнта та апарату')
                    ->schema([
                        Forms\Components\TextInput::make('client')->label('Клієнт'),
                        Forms\Components\TextInput::make('phone')->label('Телефон')->tel(),
                        Forms\Components\TextInput::make('aparat')->label('Кавоварка')->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Технічні деталі та коментарі')
                    ->schema([
                        Forms\Components\Textarea::make('defect')->label('Заявлені дефекти')->default('-'),
                        Forms\Components\Textarea::make('fact_defect')->label('Виявлені дефекти')->default('-'),
                        Forms\Components\TextInput::make('empty')
                            ->label('Комплектуючі')
                            ->columnSpanFull()
                            ->default('-'),
                        Forms\Components\Textarea::make('coment')
                            ->label('Коментар')
                            ->default('-')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('№')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('client')
                    ->label('Клієнт')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Номер телефону')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('aparat')
                    ->label('Апарат')
                    ->limit(30)
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->options(Blanck::STATUSES),
                Tables\Columns\TextColumn::make('data')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn ($query) => 
                $query->orderBy('data', 'desc')
                    ->orderBy('status', 'asc') 
            )
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Blanck::STATUSES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlancks::route('/'),
            'create' => Pages\CreateBlanck::route('/create'),
            'edit' => Pages\EditBlanck::route('/{record}/edit'),
        ];
    }
}