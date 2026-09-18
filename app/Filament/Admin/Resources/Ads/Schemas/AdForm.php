<?php

namespace App\Filament\Admin\Resources\Ads\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                Section::make('Banner')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Internal name')
                            ->helperText('Only you see this — e.g. "Header banner — September".')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Banner image')
                            ->image()
                            ->imageEditor()
                            ->directory('ads')
                            ->maxSize(2048)
                            ->helperText('Header banner looks best around 640×100. Leave empty to use ad code instead.')
                            ->columnSpanFull(),

                        TextInput::make('link')
                            ->label('Click-through URL')
                            ->url()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Textarea::make('code')
                            ->label('Ad code')
                            ->rows(4)
                            ->helperText('AdSense or any script tag. Only used when no image is uploaded.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Placement')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('placement')
                            ->options([
                                'header'            => 'Header (beside the logo)',
                                'sidebar_top'       => 'Sidebar — top',
                                'in_content'        => 'Inside article body',
                                'between_sections'  => 'Between homepage sections',
                                'footer'            => 'Footer',
                            ])
                            ->required()
                            ->native(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        DatePicker::make('starts_at')
                            ->label('Show from')
                            ->native(false)
                            ->helperText('Leave empty to start immediately.'),

                        DatePicker::make('ends_at')
                            ->label('Show until')
                            ->native(false)
                            ->helperText('Leave empty to run indefinitely.'),

                        TextInput::make('sort_order')
                            ->label('Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lowest number wins when several banners share a placement.'),
                    ]),
            ]);
    }
}
