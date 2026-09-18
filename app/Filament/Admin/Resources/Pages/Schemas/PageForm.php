<?php

namespace App\Filament\Admin\Resources\Pages\Schemas;

use App\Support\BanglaSlug;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Page')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(200)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                        if ($operation === 'create' && filled($state)) {
                                            $set('slug', BanglaSlug::make($state));
                                        }
                                    })
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->helperText('The page will live at /page/your-slug')
                                    ->maxLength(191)
                                    ->unique(ignoreRecord: true)
                                    ->columnSpanFull(),

                                RichEditor::make('body')
                                    ->label('Content')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline', 'strike',
                                        'h2', 'h3', 'blockquote',
                                        'bulletList', 'orderedList',
                                        'link', 'undo', 'redo',
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Section::make('SEO')
                            ->collapsed()
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Meta title')
                                    ->maxLength(60)
                                    ->helperText('Keep under 60 characters')
                                    ->columnSpanFull(),

                                Textarea::make('meta_description')
                                    ->label('Meta description')
                                    ->maxLength(160)
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Publish')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'draft'     => 'Draft',
                                        'published' => 'Published',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->native(false)
                                    ->helperText('Drafts are hidden from the site and skipped in menus.'),

                                Toggle::make('show_in_footer')
                                    ->label('List in the footer')
                                    ->helperText('Only used when no footer menu has been built.'),

                                TextInput::make('sort_order')
                                    ->label('Order')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }
}
