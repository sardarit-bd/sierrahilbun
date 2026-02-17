<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Services\ProductImageService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput; // New for Benefits
use Filament\Forms\Components\Textarea; // New
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // --- Left Column (Content & Variants) ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Basic Information')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Set $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),

                                TextInput::make('subtitle')
                                    ->label('Subtitle / Tagline')
                                    ->placeholder('e.g. Gallon Hose-Connect Application (2-pack)')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Product::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                                
                                Select::make('category_id')
                                    ->required()
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),

                                RichEditor::make('description')
                                    ->columnSpanFull()
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'h2', 'h3']),
                            ])->columns(2),

        
                        Section::make('Product Variants')
                            ->description('Manage sizes, prices, and stock levels.')
                            ->schema([
                                Repeater::make('variants')
                                    ->relationship() 
                                    ->schema([
                                        TextInput::make('size_label')
                                            ->label('Size Label')
                                            ->placeholder('e.g. 1 Gallon')
                                            ->required(),
                                        
                                        TextInput::make('sku')
                                            ->label('Variant SKU')
                                            ->required()
                                            ->unique(ignoreRecord: true),

                                        TextInput::make('size_volume_oz')
                                            ->label('Volume (oz)')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('price')
                                            ->label('Price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required(),

                                        TextInput::make('compare_at_price')
                                            ->label('Original Price')
                                            ->helperText('Shown as crossed out')
                                            ->numeric()
                                            ->prefix('$'),

                                        TextInput::make('stock_quantity')
                                            ->label('Stock')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),

                                        Toggle::make('is_default')
                                            ->label('Selected by default')
                                            ->inline(false),
                                        
                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0)
                                            ->hidden(), 
                                    ])
                                    ->columns(3)
                                    ->orderColumn('sort_order')
                                    ->defaultItems(1)
                                    ->itemLabel(fn (array $state): ?string => $state['size_label'] ?? null),
                            ]),

                        Section::make('Marketing Details')
                            ->collapsed()
                            ->schema([
                                TagsInput::make('benefits')
                                    ->label('Key Benefits')
                                    ->placeholder('Type and hit Enter (e.g. Kills on Contact)')
                                    ->columnSpanFull(),

                                Textarea::make('ingredients')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                RichEditor::make('usage_instructions')
                                    ->label('How to Use')
                                    ->toolbarButtons(['bold', 'bulletList', 'orderedList'])
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // --- Right Column (Settings, Images, Specs) ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active on Store')
                                    ->default(true),
                                
                                // Read-only Stats (Calculated by Observer)
                                TextInput::make('rating_avg')
                                    ->label('Average Rating')
                                    ->disabled()
                                    ->numeric(),
                                
                                TextInput::make('reviews_count')
                                    ->label('Total Reviews')
                                    ->disabled()
                                    ->numeric(),
                            ]),

                        Section::make('Pricing Overview')
                            ->description('Base display prices')
                            ->schema([
                                TextInput::make('base_price')
                                    ->numeric()
                                    ->prefix('$'),
                                TextInput::make('discount_price')
                                    ->numeric()
                                    ->prefix('$'),
                            ]),

                        Section::make('Images')
                            ->schema([
                                Repeater::make('images')
                                    ->relationship()
                                    ->schema([
                                        FileUpload::make('image_url')
                                            ->image()
                                            ->disk('public')
                                            ->directory('products')
                                            ->maxSize(5120)
                                            ->imageEditor()
                                            ->saveUploadedFileUsing(function ($file) {
                                                return app(ProductImageService::class)->optimizeAndStore($file);
                                            })
                                            ->required(),
                                        Checkbox::make('is_primary'),
                                    ])
                                    ->grid(1)
                                    ->minItems(1)
                                    ->maxItems(10)
                                    ->reorderableWithDragAndDrop()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => ($state['is_primary'] ?? false) ? '⭐ Primary' : 'Image'),
                            ]),

                        Section::make('Tech Specs')
                            ->schema([
                                TextInput::make('sku')
                                    ->label('Master SKU'),
                                TextInput::make('coverage_sqft')
                                    ->label('Coverage (sq ft)')
                                    ->numeric(),
                                TextInput::make('application_rate_oz_per_1k')
                                    ->label('App Rate (oz/1k)')
                                    ->numeric(),
                            ]),
                    ]),
            ]);
    }
}