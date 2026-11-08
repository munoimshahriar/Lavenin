<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Concerns\TranslatableForm;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Support\ImageProcessor;
use App\Support\VideoEmbed;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    use RestrictedToRoles, TranslatableForm;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static array $viewRoles = ['owner', 'manager'];

    protected static array $deleteRoles = ['owner', 'manager'];

    public static function getNavigationBadge(): ?string
    {
        $low = Product::lowStock()->where('is_active', true)->count();

        return $low ? (string) $low : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Low stock';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Details')->schema([
                static::localeTabs(fn (string $l) => [
                    Forms\Components\TextInput::make("name.{$l}")->label('Name')->required()->maxLength(160),
                    Forms\Components\RichEditor::make("description.{$l}")->label('Description')
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                        ->maxLength(10000),
                ]),
                Forms\Components\Select::make('category_id')->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->searchable()->preload(),
                Forms\Components\TextInput::make('age_range')->placeholder('0-12 months')->maxLength(50),
                Forms\Components\TextInput::make('slug')->helperText('Leave empty to generate from the English name.')
                    ->alphaDash()->maxLength(180)->unique(ignoreRecord: true),
            ])->columns(2),

            Forms\Components\Section::make('Price & stock')->schema([
                Forms\Components\TextInput::make('price')->label('Price (৳)')->required()->integer()->minValue(0)->maxValue(10_000_000),
                Forms\Components\TextInput::make('compare_at_price')->label('Was price (৳)')->helperText('Shown crossed out when higher than price.')
                    ->integer()->minValue(0)->maxValue(10_000_000),
                Forms\Components\TextInput::make('sku')->label('SKU')->maxLength(64)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('stock')->required()->integer()->default(0),
                Forms\Components\TextInput::make('low_stock_threshold')->required()->integer()->minValue(0)->default(5),
                Forms\Components\Toggle::make('is_active')->label('Visible in shop')->default(true),
                Forms\Components\Toggle::make('is_featured')->label('Featured on home page'),
            ])->columns(3),

            Forms\Components\Section::make('Media')->schema([
                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->orderColumn('sort')
                    ->reorderable()
                    ->grid(3)
                    ->defaultItems(0)
                    ->addActionLabel('Add image')
                    ->schema([
                        Forms\Components\FileUpload::make('path')->hiddenLabel()
                            ->image()
                            ->disk('public')
                            ->directory(ImageProcessor::TMP_DIR)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->required(),
                    ]),
                Forms\Components\TextInput::make('video_url')->label('YouTube / Facebook video link')
                    ->url()->maxLength(500)
                    ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                        if (filled($value) && ! VideoEmbed::from($value)) {
                            $fail('Only YouTube or Facebook video links are supported.');
                        }
                    }),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['firstImage', 'category']))
            ->columns([
                Tables\Columns\ImageColumn::make('firstImage.thumb_path')->disk('public')->label('')->square(),
                Tables\Columns\TextColumn::make('name')->searchable(query: fn ($query, $search) => $query->where(fn ($w) => $w->searchName($search)->orWhere('sku', 'like', "%{$search}%")))->limit(40),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('price')->formatStateUsing(fn ($state) => '৳'.number_format($state))->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable()->badge()
                    ->color(fn (Product $record) => $record->isLowStock() ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Visible'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'slug'),
                Tables\Filters\Filter::make('low_stock')->label('Low stock')->query(fn (Builder $query) => $query->lowStock()),
                Tables\Filters\TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
