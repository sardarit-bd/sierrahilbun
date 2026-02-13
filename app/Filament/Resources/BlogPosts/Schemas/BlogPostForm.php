<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)                    // top-level schema is a 3-column grid
            ->components([

                // ── MAIN CONTENT (2 of 3 columns) ─────────────────────────

                Section::make('Post Content')
                    ->description('The core content and identity of your blog post.')
                    ->icon('heroicon-o-document-text')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([

                        TextInput::make('title')
                            ->label('Title')
                            ->placeholder('Enter a compelling post title…')
                            ->required()
                            ->maxLength(160)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) =>
                                $set('slug', Str::slug($state ?? ''))
                            )
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('auto-generated-from-title')
                            ->required()
                            ->maxLength(160)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->prefix('blog/')
                            ->helperText('Auto-filled from the title. Must be unique.')
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->label('Excerpt')
                            ->placeholder('A short summary shown in listings and social previews…')
                            ->rows(3)
                            ->maxLength(300)
                            ->helperText('Max 300 characters — used for meta descriptions and card previews.')
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3'],
                                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                ['grid', 'attachFiles'],
                                ['undo', 'redo'],
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('blog/attachments')
                            ->columnSpanFull(),

                        TagsInput::make('tags')
                            ->label('Tags')
                            ->placeholder('Add a tag and press Enter…')
                            ->helperText('Tags help readers discover related posts.')
                            ->columnSpanFull(),
                    ]),

                // ── SIDEBAR (1 of 3 columns) ───────────────────────────────

                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([

                        // Publishing
                        Section::make('Publishing')
                            ->description('Control who wrote it and when it goes live.')
                            ->icon('heroicon-o-rocket-launch')
                            ->schema([

                                Select::make('author_id')
                                    ->label('Author')
                                    ->relationship('author', 'name')
                                    ->default(fn () => auth()->id())
                                    ->dehydrated()
                                    ->disabled()
                                    ->native(false),

                                Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft'     => 'Draft',
                                        'review'    => 'In Review',
                                        'scheduled' => 'Scheduled',
                                        'published' => 'Published',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->native(false)
                                    ->live(),

                                DateTimePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->seconds(false)
                                    ->native(false)
                                    ->helperText('Leave blank to publish immediately.')
                                    ->visible(fn ($get) => in_array($get('status'), ['scheduled', 'published'])),

                                Toggle::make('is_published')
                                    ->label('Publicly visible')
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->inline(false),
                            ]),

                        // Featured Image
                        Section::make('Featured Image')
                            ->description('Shown at the top of the post and in social previews.')
                            ->icon('heroicon-o-photo')
                            ->collapsible()
                            ->schema([

                                FileUpload::make('featured_image_url')
                                    ->label(false)
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                                    ->disk('public')
                                    ->directory('blog/featured')
                                    ->visibility('public')
                                    ->maxSize(4096)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->helperText('JPEG · PNG · WebP — max 4 MB, 16:9 recommended'),

                                TextInput::make('featured_image_alt')
                                    ->label('Alt Text')
                                    ->placeholder('Describe the image for screen readers…')
                                    ->maxLength(125),
                            ]),

                    ]),
            ]);
    }
}