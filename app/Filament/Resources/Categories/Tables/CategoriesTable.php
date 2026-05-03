<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CategoriesTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('slug')
                ->searchable(),
            TextColumn::make('description')
                ->limit(50),
            IconColumn::make('is_active')
                ->boolean(),
            TextColumn::make('created_at')
                ->dateTime('d M Y')
                ->sortable(),
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('is_active')
                ->options([
                    '1' => 'Active',
                    '0' => 'Inactive',
                ]),
        ];
    }

    public static function getActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}