<?php

namespace App\Filament\Resources\TahsinatSections\Tables;

use App\Models\TahsinatCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class TahsinatSectionsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')->label('Name')->searchable(),
            TextColumn::make('category.name')->label('Category'),
            IconColumn::make('order_randomly')->label('Random')->boolean(),
            TextColumn::make('display_order')->sortable(),
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('tahsinat_category_id')
                ->label('Category')
                ->options(fn () => TahsinatCategory::ordered()->get()->pluck('name', 'id')),
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
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ];
    }
}
