<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\FeedbackResource\Pages;
use App\Models\User\Feedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Клієнти та Користувачі';
    protected static ?string $modelLabel = 'Зворотний зв’язок';
    protected static ?string $pluralModelLabel = 'Повідомлення';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Деталі повідомлення')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Ім’я'),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('call')
                                    ->label('Зателефонувати')
                                    ->icon('heroicon-m-phone')
                                    ->color('success')
                                    ->url(function ($state) {
                                        if (blank($state)) {
                                            return null;
                                        }
                                        
                                        $cleanNumber = preg_replace('/[^0-9+]/', '', $state);
                                        
                                        return "tel:{$cleanNumber}";
                                    })
                            ),

                        Forms\Components\TextInput::make('subject')
                            ->label('Тема'),

                        Forms\Components\Toggle::make('send')
                            ->label('Опрацьовано')
                            ->helperText('Позначте, якщо ви вже відповіли клієнту'),

                        Forms\Components\Textarea::make('message')
                            ->label('Текст повідомлення')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('send')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Автор')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->copyable() // Дозволяє швидко скопіювати номер
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Тема')
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Нові повідомлення зверху
            ->filters([
                Tables\Filters\TernaryFilter::make('send')
                    ->label('Тільки неопрацьовані')
                    ->placeholder('Всі повідомлення')
                    ->trueLabel('Опрацьовані')
                    ->falseLabel('Очікують'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
        ];
    }
}