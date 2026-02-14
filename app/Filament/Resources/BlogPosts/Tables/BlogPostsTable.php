<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Pest\Support\View;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image_url')
                    ->label('')
                    ->disk('public')
                    ->visibility('public')
                    ->width(56)
                    ->height(40)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=No+Image&background=e5e7eb&color=6b7280&size=56'),


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

                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'review'    => 'info',
                        default     => 'gray',  
                    })
                    ->formatStateUsing(fn (string $state) => str($state)->title())
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('created_at', 'desc')

            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'review'    => 'In Review',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                    ])
                    ->native(false),

                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                TernaryFilter::make('is_published')
                    ->label('Visibility')
                    ->placeholder('All posts')
                    ->trueLabel('Published only')
                    ->falseLabel('Unpublished only'),

                Filter::make('published_at')
                    ->label('Published this month')
                    ->query(fn (Builder $query) => $query->whereMonth('published_at', now()->month)
                        ->whereYear('published_at', now()->year)
                    ),
            ])

            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
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