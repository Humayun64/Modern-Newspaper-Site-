<?php

namespace App\Filament\Admin\Resources\Ads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('placement')
            ->columns([
                ImageColumn::make('image')
                    ->label('Banner')
                    ->height(34)
                    ->width(110),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('placement')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                ToggleColumn::make('is_active')->label('Active'),

                TextColumn::make('starts_at')
                    ->label('From')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('ends_at')
                    ->label('Until')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->filters([
                SelectFilter::make('placement')
                    ->options([
                        'header'           => 'Header',
                        'sidebar_top'      => 'Sidebar — top',
                        'in_content'       => 'Inside article body',
                        'between_sections' => 'Between homepage sections',
                        'footer'           => 'Footer',
                    ])
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
