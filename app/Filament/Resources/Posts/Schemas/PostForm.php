<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship(
                        'category',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->where('type', Category::TYPES[0]),
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('Pilih kategori')
                    ->required(),

                DatePicker::make('published_at')
                    ->label('Tanggal terbit')
                    ->required(),

                Toggle::make('is_published')
                    ->label('Dipublikasikan')
                    ->helperText('Nonaktifkan untuk menyimpan sebagai draf — draf tidak tampil di situs publik.')
                    ->default(true),

                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Kosongkan untuk dibuat otomatis dari judul.')
                    ->unique(ignoreRecord: true)
                    ->maxLength(160),

                TextInput::make('art')
                    ->label('Variasi ilustrasi (0-5)')
                    ->helperText('Angka 0-5 untuk ilustrasi gradien — hanya tampil bila foto kosong.')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->default(0),

                FileUpload::make('image_path')
                    ->label('Foto artikel')
                    ->image()
                    ->disk('public')
                    ->directory('photos/posts')
                    ->maxSize(4096)
                    ->helperText('JPG, PNG, atau WebP maksimal 4 MB. Tampil pada daftar berita, artikel, dan ikan media sosial. Kosongkan untuk memakai ilustrasi gradien.')
                    ->columnSpanFull(),

                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->helperText('Tampil pada daftar berita dan kartu kabar.')
                    ->required()
                    ->rows(3)
                    ->maxLength(4000)
                    ->columnSpanFull(),

                Textarea::make('body')
                    ->label('Isi artikel')
                    ->helperText('Satu baris = satu paragraf.')
                    ->rows(14)
                    ->maxLength(60000)
                    ->columnSpanFull(),
            ]);
    }
}
