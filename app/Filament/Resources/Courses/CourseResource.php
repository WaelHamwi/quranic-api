<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\ManageCourses;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title.ar')->label('Title (Arabic)')->required()->maxLength(255),
            TextInput::make('title.en')->label('Title (English)')->required()->maxLength(255),
            Textarea::make('description.ar')->label('Description (Arabic)')->rows(3),
            Textarea::make('description.en')->label('Description (English)')->rows(3),
            TextInput::make('instructor_name')->maxLength(255),
            TextInput::make('price')->numeric()->minValue(0)->prefix('$'),
            DatePicker::make('start_date'),
            TextInput::make('whatsapp_link')->url()->maxLength(255),
            Toggle::make('is_coming_soon')->default(true),
            Toggle::make('is_active')->default(true),
            TextInput::make('display_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Title')->searchable(),
                TextColumn::make('instructor_name')->label('Instructor'),
                TextColumn::make('price')->money('USD'),
                IconColumn::make('is_coming_soon')->label('Coming Soon')->boolean(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('display_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCourses::route('/')];
    }
}
