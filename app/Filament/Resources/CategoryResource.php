<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Concerns\TranslatableForm;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    use RestrictedToRoles, TranslatableForm;

    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    protected static array $viewRoles = ['owner', 'manager'];

    protected static array $deleteRoles = ['owner', 'manager'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            static::localeTabs(fn (string $l) => [
                Forms\Components\TextInput::make("name.{$l}")->label('Name')->required()->maxLength(120),
                Forms\Components\Textarea::make("description.{$l}")->label('Description')->rows(3)->maxLength(1000),
            ]),
            Forms\Components\TextInput::make('slug')
                ->helperText('Leave empty to generate from the English name.')
                ->alphaDash()->maxLength(140)
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('sort')->numeric()->default(0)->minValue(0),
            Forms\Components\Toggle::make('is_active')->label('Visible in shop')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(query: fn ($query, $search) => $query->searchName($search)),
                Tables\Columns\TextColumn::make('slug')->color('gray'),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->label('Products'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Visible'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
