<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use App\Models\ProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Checkbox;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Basic Information')
                    ->schema([
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
                            ->rules(['alpha_dash'])
                            ->helperText('Auto-generated from name, but can be customized')
                            ->columnSpanFull(),

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Select a product category'),

                        TextInput::make('sku')
                            ->label('Master SKU')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->alphaNum()
                            ->helperText('Unique product identifier')
                            ->suffix('📋'),

                        TextInput::make('base_price')
                            ->label('Base Price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Starting price for this product'),

                        TextInput::make('discount_price')
                            ->label('Discount Price')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Sale price for this product')
                            ->lte('base_price'),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                Section::make('Product Images')
    ->description('Upload multiple product images. Drag to reorder, first image is primary.')
    ->schema([
        Repeater::make('images')
            ->relationship()
            ->schema([
                FileUpload::make('image_url')
                    ->label('Image')
                    ->image()
                    ->directory('products')
                    ->maxSize(5120)
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                        '4:3',
                        '16:9',
                    ])
                    ->imagePreviewHeight('200')
                    ->panelLayout('compact')
                    ->saveUploadedFileUsing(function ($file) {
                        return app(ProductImageService::class)->optimizeAndStore($file);
                    })
                    ->required()
                    ->columnSpan(3),

                Checkbox::make('is_primary')
                    ->label('Primary')
                    ->inline(false)
                    ->columnSpan(1),
            ])
            ->columns(4)
            ->orderColumn('sort_order')
            ->reorderable()
            ->reorderableWithButtons()
            ->reorderableWithDragAndDrop()
            ->collapsible()
            ->collapsed(false)
            ->cloneable()
            ->itemLabel(fn (array $state): ?string => 
                ($state['is_primary'] ?? false) 
                    ? '⭐ Primary Image' 
                    : 'Image'
            )
            ->addActionLabel('📸 Add Image')
            ->defaultItems(1)
            ->minItems(0)
            ->maxItems(10)
            ->deleteAction(
                fn ($action) => $action
                    ->requiresConfirmation()
                    ->modalHeading('Delete image')
                    ->modalDescription('This will permanently delete the image.')
                    ->label('Delete')
            )
            ->addAction(
                fn ($action) => $action
                    ->label('Add Image')
                    ->icon('heroicon-o-plus-circle')
            )
            ->grid(1)
            ->columnSpanFull(),
    ])
    ->columnSpanFull()
    ->icon('heroicon-o-photo')
    ->collapsible()
    ->persistCollapsed()
    ->compact(),

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
                            ->helperText('Detailed product information shown to customers')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Lawn Care Specifications')
                    ->description('Application and coverage calculations')
                    ->schema([
                        TextInput::make('coverage_sqft')
                            ->label('Coverage Area')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('sq ft')
                            ->helperText('How much lawn area one unit covers')
                            ->columnSpan(1),

                        TextInput::make('application_rate_oz_per_1k')
                            ->label('Application Rate')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('oz per 1,000 sq ft')
                            ->helperText('Rate calculated from Python script')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}