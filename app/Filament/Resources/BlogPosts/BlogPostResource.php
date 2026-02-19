<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Filament\Resources\BlogPosts\Schemas\BlogPostForm;
use App\Filament\Resources\BlogPosts\Tables\BlogPostsTable;
use App\Models\BlogPost;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string | UnitEnum | null $navigationGroup = 'Blog Management';
    protected static ?string $navigationLabel = 'Blogs';

    public static function form(Schema $schema): Schema
    {
        return BlogPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogPostsTable::configure($table);
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
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── MAIN CONTENT (full width, 3 columns) ──────────────────

                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([

                        // Hero Banner
                        Section::make()
                            ->hiddenLabel()
                            ->schema([
                                ImageEntry::make('featured_image_url')
                                    ->hiddenLabel()
                                    ->disk('public')
                                    ->visibility('public')
                                    ->width('100%')
                                    ->height(400)
                                    ->extraImgAttributes([
                                        'class'   => 'w-full object-cover rounded-xl',
                                        'loading' => 'lazy',
                                        'alt'     => 'Featured image',
                                    ])
                                    ->defaultImageUrl(
                                        fn ($record) => 'https://ui-avatars.com/api/?name='
                                            . urlencode($record?->title ?? 'No Image')
                                            . '&background=e5e7eb&color=9ca3af&size=800&bold=true'
                                    )
                                    ->columnSpanFull(),
                            ])
                            ->extraAttributes(['class' => 'overflow-hidden !p-0 shadow-sm']),

                        // Title, Slug, Excerpt & Image Alt
                        Section::make('Post Identity')
                            ->description('Core details that identify this post.')
                            ->icon('heroicon-o-identification')
                            ->collapsible()
                            ->columns(2)
                            ->schema([

                                TextEntry::make('title')
                                    ->label('Title')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),

                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->icon('heroicon-m-link')
                                    ->iconPosition(IconPosition::Before)
                                    ->copyable()
                                    ->copyMessage('Slug copied!')
                                    ->color('gray')
                                    ->fontFamily(FontFamily::Mono)
                                    ->prefix('blog/'),

                                TextEntry::make('tags')
                                    ->label('Tags')
                                    ->badge()
                                    ->separator(',')
                                    ->color('info')
                                    ->placeholder('No tags'),

                                TextEntry::make('excerpt')
                                    ->label('Excerpt')
                                    ->placeholder('No excerpt provided.')
                                    ->columnSpanFull()
                                    ->color('gray'),

                                TextEntry::make('featured_image_alt')
                                    ->label('Image Alt Text')
                                    ->icon('heroicon-m-photo')
                                    ->iconPosition(IconPosition::Before)
                                    ->placeholder('No alt text set')
                                    ->color('gray')
                                    ->columnSpanFull(),
                            ]),

                        // Full Content
                        Section::make('Content')
                            ->description('The full body of the post.')
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->schema([
                                TextEntry::make('content')
                                    ->hiddenLabel()
                                    ->html()
                                    ->columnSpanFull()
                                    ->extraAttributes([
                                        'class' => 'prose prose-sm dark:prose-invert max-w-none [&_img]:rounded-lg [&_a]:text-primary-600 [&_blockquote]:border-primary-300',
                                    ]),
                            ]),
                    ]),

                // ── BOTTOM ROW: Publishing (2 cols) + Record Info (1 col) ──

                Section::make('Publishing')
                    ->columnSpan(2)
                    ->description('Status, authorship & timing.')
                    ->icon('heroicon-o-rocket-launch')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'published' => 'success',
                                'scheduled' => 'warning',
                                'review'    => 'info',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'published' => 'Published',
                                'scheduled' => 'Scheduled',
                                'review'    => 'In Review',
                                default     => 'Draft',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'published' => 'heroicon-m-check-circle',
                                'scheduled' => 'heroicon-m-clock',
                                'review'    => 'heroicon-m-eye',
                                default     => 'heroicon-m-pencil',
                            }),

                        TextEntry::make('is_published')
                            ->label('Visibility')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Public' : 'Hidden')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                            ->icon(fn (bool $state): string => $state
                                ? 'heroicon-m-eye'
                                : 'heroicon-m-eye-slash'
                            ),

                        TextEntry::make('author.name')
                            ->label('Author')
                            ->icon('heroicon-m-user-circle')
                            ->iconPosition(IconPosition::Before)
                            ->weight(FontWeight::Medium),

                        TextEntry::make('category.name')
                            ->label('Category')
                            ->icon('heroicon-m-folder')
                            ->iconPosition(IconPosition::Before)
                            ->badge()
                            ->color('primary')
                            ->placeholder('Uncategorised'),

                        TextEntry::make('published_at')
                            ->label('Published At')
                            ->icon('heroicon-m-calendar-days')
                            ->iconPosition(IconPosition::Before)
                            ->dateTime('M j, Y · H:i')
                            ->placeholder('Not scheduled')
                            ->since()
                            ->dateTimeTooltip('M j, Y · H:i'),
                    ]),

                Section::make('Record Info')
                    ->columnSpan(1)
                    ->description('System-managed timestamps.')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->icon('heroicon-m-plus-circle')
                            ->iconPosition(IconPosition::Before)
                            ->since()
                            ->dateTimeTooltip('M j, Y · H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->icon('heroicon-m-arrow-path')
                            ->iconPosition(IconPosition::Before)
                            ->since()
                            ->dateTimeTooltip('M j, Y · H:i'),
                    ]),
            ]);
    }
}
