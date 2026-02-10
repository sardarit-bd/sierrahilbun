<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use App\Models\ProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Set;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            // ->createOptionForm([
                            //     TextInput::make('name')
                            //         ->required()
                            //         ->maxLength(255)
                            //         ->live(onBlur: true)
                            //         ->afterStateUpdated(fn (string $operation, $state, Set $set) => 
                            //             $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            //         ),
                            //     TextInput::make('slug')
                            //         ->required()
                            //         ->maxLength(255)
                            //         ->unique(ProductCategory::class, 'slug', ignoreRecord: true),
                            // ])
                            ->nullable(),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            )
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->helperText('Auto-generated from name, but can be customized')
                            ->columnSpanFull(),

                        TextInput::make('sku')
                            ->label('SKU (Master SKU)')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->helperText('Unique product identifier')
                            ->columnSpan(1),

                        TextInput::make('base_price')
                            ->label('Base Price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('TurfTec Logic')
                    ->description('Lawn care specific calculations')
                    ->schema([
                        TextInput::make('coverage_sqft')
                            ->label('Coverage (sq ft)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('How much area 1 unit covers')
                            ->columnSpan(1),

                        TextInput::make('application_rate_oz_per_1k')
                            ->label('Application Rate (oz per 1,000 sq ft)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('From Python calculation script')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Description')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Product Description')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'h2',
                                'h3',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active products are shown on the website')
                            ->inline(false),
                    ]),
            ]);
    }
}
