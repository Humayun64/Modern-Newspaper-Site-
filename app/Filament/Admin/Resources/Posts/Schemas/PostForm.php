<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use App\Support\BanglaSlug;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ---------------- LEFT ----------------
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([

                        Section::make('Article')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(500)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                        if ($operation === 'create' && filled($state)) {
                                            $set('slug', BanglaSlug::make($state));
                                        }
                                    })
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->helperText('Generated from the title. Edit if needed.')
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

                                Textarea::make('excerpt')
                                    ->helperText('Leave empty to generate automatically from the content.')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('SEO')
                            ->description('How this article appears in search results')
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
                                    ->helperText('Keep under 160 characters')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // ---------------- RIGHT ----------------
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([

                        Section::make('Publish')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'draft'     => 'Draft',
                                        'pending'   => 'Pending review',
                                        'published' => 'Published',
                                        'scheduled' => 'Scheduled',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('published_at')
                                    ->label('Publish date')
                                    ->seconds(false)
                                    ->displayFormat('d M Y, h:i A')
                                    ->helperText('Leave empty to publish now.'),
                            ]),

                        Section::make('Taxonomy')
                            ->schema([
                                Select::make('category_id')
                                    ->label('Primary category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                Select::make('categories')
                                    ->label('Additional categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->native(false),

                                Select::make('tags')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                    ])
                                    ->native(false),

                                Select::make('author_id')
                                    ->label('Author')
                                    ->relationship('author', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(auth()->id())
                                    ->native(false),
                            ]),

                        Section::make('Featured image')
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label('')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('posts')
                                    ->maxSize(4096),

                                TextInput::make('image_caption')
                                    ->label('Caption')
                                    ->maxLength(500),

                                TextInput::make('image_credit')
                                    ->label('Credit')
                                    ->placeholder('Collected'),
                            ]),

                        Section::make('Post type')
                            ->schema([
                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'standard' => 'Standard',
                                        'video'    => 'Video',
                                        'gallery'  => 'Photo gallery',
                                        'podcast'  => 'Podcast',
                                        'live'     => 'Live blog',
                                    ])
                                    ->default('standard')
                                    ->required()
                                    ->live()
                                    ->native(false),

                                TextInput::make('video_url')
                                    ->label('Video URL')
                                    ->url()
                                    ->visible(fn ($get) => $get('type') === 'video'),

                                TextInput::make('audio_url')
                                    ->label('Audio URL')
                                    ->url()
                                    ->visible(fn ($get) => $get('type') === 'podcast'),
                            ]),

                        Section::make('Homepage placement')
                            ->schema([
                                Toggle::make('is_breaking')->label('Breaking news'),
                                Toggle::make('is_featured')->label('Main slider'),
                                Toggle::make('is_editors_pick')->label("Editor's pick"),
                                Toggle::make('is_trending')->label('Trending')->live(),

                                TextInput::make('trending_position')
                                    ->label('Trending position (1-5)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(5)
                                    ->visible(fn ($get) => (bool) $get('is_trending')),

                                Toggle::make('is_sponsored')->label('Sponsored / guest post'),
                            ]),
                    ]),
            ]);
    }
}