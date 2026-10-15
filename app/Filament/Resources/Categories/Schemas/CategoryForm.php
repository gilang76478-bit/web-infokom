<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama kategori')
                    ->helperText('Slug dibuat otomatis dari nama kategori.')
                    ->required()
                    ->maxLength(120),

                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'berita' => 'Berita',
                        'agenda' => 'Agenda',
                        'galeri' => 'Galeri',
                    ])
                    ->default(Category::TYPES[0])
                    ->native(false)
                    ->required(),
            ]);
    }
}
