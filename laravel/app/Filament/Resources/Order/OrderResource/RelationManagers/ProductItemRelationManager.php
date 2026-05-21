<?php

namespace App\Filament\Resources\Order\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use App\Filament\Resources\Catalog\ProductResource;

class ProductItemRelationManager extends RelationManager
{
    protected static string $relationship = 'productItems';
    protected static ?string $title = 'Товари у замовленні';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('product_image')
                    ->label('Фото товару')
                    ->content(function ($record) {
                        $url = '';
                        if ($record?->product?->main_image) {
                            $url = asset('/storage/' . $record->product->main_image);
                        }

                        return new \Illuminate\Support\HtmlString("
                            <div class='flex items-center justify-center overflow-hidden rounded-lg border border-gray-300' style='width: 200px; height: 200px;'>
                                <img id='addProductImg' src='{$url}' style='object-fit: cover; width: 100%; height: 100%;'>
                            </div>
                        ");
                    }),
                Forms\Components\Select::make('product_id')
                    ->label('Товар')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Catalog\Product::where('name', 'like', "%{$search}%")
                            ->orWhere('internal_sku', 'like', "%{$search}%")
                            ->orWhere('manufacturer_sku', 'like', "%{$search}%")
                            ->orWhere('atel_sku', 'like', "%{$search}%")
                            ->orWhere('search_words', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id');
                    }),
                
                Forms\Components\TextInput::make('quantity')
                    ->label('Кількість')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Ціна за одиницю')
                    ->numeric()
                    ->prefix('₴')
                    ->required(),

                
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                ImageColumn::make('product.main_image')
                    ->label('Фото')
                    ->size(50)
                    ->getStateUsing(fn ($record) => asset('/storage/' . $record->product->main_image)),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Назва товару')
                    ->lineClamp(2)
                    ->wrap()
                    ->state(fn ($record) => $record->product->internal_sku ." / " .$record->product->one_c_path ." / " .$record->product->name),

                Tables\Columns\TextColumn::make('inStock')
                    ->label('Склад')
                    ->state(fn ($record) => $record->product->quantity . "шт."),

                Tables\Columns\TextInputColumn::make('quantity')
                    ->label('Кількість')
                    ->type('number') 
                    ->rules(['required', 'numeric', 'min:0']),

                
                Tables\Columns\TextInputColumn::make('price')
                    ->label('Ціна')
                    ->type('number') 
                    ->rules(['required', 'numeric', 'min:0']),

                    


            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('Переглянути')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn ($record): string => ProductResource::getUrl('edit', ['record' => $record->product_id]))
                    ->openUrlInNewTab()
                    ->extraAttributes(fn ($record) => [
                        'data-order_id' => "{$record->order_id}",
                        'data-product_id' => "{$record->product_id}",
                        'data-quantity' => "{$record->quantity}",
                    ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
            ]);
    }
}