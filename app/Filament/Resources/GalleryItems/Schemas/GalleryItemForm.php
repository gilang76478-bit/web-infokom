<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Keterangan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('category_id')
                    ->label('Kategori galeri')
                    ->relationship(
                        'category',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->where('type', 'galeri'),
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('Pilih kategori')
                    ->required(),

                Select::make('size')
                    ->label('Ukuran tile')
                    ->options([
                        '' => 'Standar',
                        'w' => 'Lebar (2 kolom)',
                        't' => 'Vertikal',
                    ])
                    ->default('')
                    ->native(false),

                FileUpload::make('image_path')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('gallery')
                    ->maxSize(2048)
                    ->helperText('JPG, PNG, WebP, atau GIF maksimal 2 MB. Kosongkan bila memakai ilustrasi bawaan (art).')
                    ->columnSpanFull(),

                TextInput::make('art')
                    ->label('Variasi ilustrasi (0-5)')
                    ->helperText('Tampil sebagai cadangan bila foto belum diunggah.')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->default(0),
            ]);
    }
}
