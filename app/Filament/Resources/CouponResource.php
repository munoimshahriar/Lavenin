<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    protected static array $viewRoles = ['owner', 'manager'];

    protected static array $deleteRoles = ['owner', 'manager'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->maxLength(40)->alphaDash()->unique(ignoreRecord: true)
                ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
            Forms\Components\Select::make('type')->options(['percent' => 'Percent (%)', 'fixed' => 'Fixed amount (৳)'])->required()->default('percent')->live(),
            Forms\Components\TextInput::make('value')->required()->integer()->minValue(1)
                ->maxValue(fn (Forms\Get $get) => $get('type') === 'percent' ? 100 : 1_000_000),
            Forms\Components\TextInput::make('min_order')->label('Minimum order (৳)')->integer()->minValue(0)->default(0),
            Forms\Components\DateTimePicker::make('expires_at')->label('Expires at')->native(false),
            Forms\Components\TextInput::make('usage_limit')->integer()->minValue(1)->helperText('Leave empty for unlimited.'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('value')->formatStateUsing(fn ($state, Coupon $r) => $r->type === 'percent' ? "{$state}%" : '৳'.number_format($state)),
                Tables\Columns\TextColumn::make('min_order')->formatStateUsing(fn ($state) => '৳'.number_format($state)),
                Tables\Columns\TextColumn::make('used_count')->label('Used')->formatStateUsing(fn ($state, Coupon $r) => $r->usage_limit ? "{$state} / {$r->usage_limit}" : $state),
                Tables\Columns\TextColumn::make('expires_at')->dateTime('d M Y')->placeholder('Never'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCoupons::route('/'),
        ];
    }
}
