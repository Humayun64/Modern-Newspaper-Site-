<?php

namespace App\Filament\Admin\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')

            /*
             * Authors see only their own work. The policy already stops them
             * opening anyone else's article, but without this they would still
             * see every headline in the list, which is both confusing and a
             * small leak of unpublished work.
             */
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if ($user && ! $user->canPublish()) {
                    $query->where('author_id', $user->id);
                }
            })

            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->height(44)
                    ->width(66),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(70)
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 320px;']),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft'     => 'gray',
                        'pending'   => 'warning',
                        'scheduled' => 'info',
                        default     => 'gray',
                    })
                    ->sortable(),

                ToggleColumn::make('is_breaking')->label('Breaking'),
                ToggleColumn::make('is_featured')->label('Slider'),

                TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Draft',
                        'pending'   => 'Pending review',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                    ])
                    ->native(false),

                SelectFilter::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    // Pointless for an author who can only see their own posts.
                    ->visible(fn () => auth()->user()?->canPublish() ?? false),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'standard' => 'Standard',
                        'video'    => 'Video',
                        'gallery'  => 'Photo gallery',
                        'podcast'  => 'Podcast',
                        'live'     => 'Live blog',
                    ])
                    ->native(false),

                Filter::make('published_between')
                    ->schema([
                        DatePicker::make('from')->label('From')->native(false),
                        DatePicker::make('until')->label('Until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('published_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('published_at', '<=', $date));
                    })
                    ->columnSpan(2),

                TrashedFilter::make()->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
