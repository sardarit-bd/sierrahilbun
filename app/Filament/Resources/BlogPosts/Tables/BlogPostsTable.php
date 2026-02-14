<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Thumbnail — small, rounded, no label clutter
                ImageColumn::make('featured_image_url')
                    ->label('')
                    ->width(56)
                    ->height(40)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=No+Image&background=e5e7eb&color=6b7280&size=56'),

                // Title + excerpt stacked
                TextColumn::make('title')
                    ->label('Post')
                    ->description(fn ($record) => $record->excerpt
                        ? str($record->excerpt)->limit(72)
                        : null
                    )
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                // Author
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user-circle')
                    ->toggleable(),

                // Category badge
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // Status badge with per-value colours
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'review'    => 'info',
                        default     => 'gray',   // draft
                    })
                    ->formatStateUsing(fn (string $state) => str($state)->title())
                    ->sortable(),

                // Published date — hidden until sorted/needed
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                // Created date — hidden by default
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('created_at', 'desc')

            ->filters([

                // Status dropdown
                SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'review'    => 'In Review',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                    ])
                    ->native(false),

                // Category dropdown
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                // Author dropdown
                SelectFilter::make('author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                // Published toggle
                TernaryFilter::make('is_published')
                    ->label('Visibility')
                    ->placeholder('All posts')
                    ->trueLabel('Published only')
                    ->falseLabel('Unpublished only'),

                // Date range
                Filter::make('published_at')
                    ->label('Published this month')
                    ->query(fn (Builder $query) => $query->whereMonth('published_at', now()->month)
                        ->whereYear('published_at', now()->year)
                    ),
            ])

            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No posts yet')
            ->emptyStateDescription('Create your first blog post to get started.');
    }
}