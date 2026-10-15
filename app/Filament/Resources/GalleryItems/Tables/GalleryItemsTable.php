<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('image_path')
                    ->label('Foto')
                    ->formatStateUsing(fn (?string $state) => filled($state)
                        ? '<img src="' . e(asset('storage/' . $state)) . '" alt="" width="48" height="48" style="border-radius:8px;object-fit:cover">'
                        : '<span style="opacity:.6">Tanpa foto</span>')
                    ->html(),
                TextColumn::make('title')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('size')
                    ->label('Ukuran')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'w' => 'Lebar',
                        't' => 'Vertikal',
                        default => 'Standar',
                    })
                    ->toggleable(),
                TextColumn::make('art')
                    ->label('Art')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
