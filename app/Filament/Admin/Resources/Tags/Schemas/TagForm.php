<?php

namespace App\Filament\Admin\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('posts_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('wp_term_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
