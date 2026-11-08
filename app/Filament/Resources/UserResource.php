<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;

/** Admin team management (owner only). Customers are not listed here. */
class UserResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Admin users';

    protected static ?string $modelLabel = 'admin user';

    protected static array $viewRoles = ['owner'];

    protected static array $deleteRoles = ['owner'];

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role(User::ADMIN_ROLES);
    }

    public static function canDelete($record): bool
    {
        return static::userHasRole(['owner']) && $record->id !== auth()->id();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(190),
            Forms\Components\TextInput::make('phone')->tel()->unique(ignoreRecord: true)->maxLength(20),
            Forms\Components\TextInput::make('password')->password()->revealable()
                ->rule(Password::min(12)->letters()->numbers())
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->helperText('At least 12 characters with letters and numbers. Leave empty to keep the current password.'),
            Forms\Components\Select::make('roles')->relationship('roles', 'name')->multiple()->preload()
                ->required()
                ->disabled(fn (?User $record) => $record?->id === auth()->id())
                ->helperText('Owner: everything · Manager: catalog, orders, coupons, reviews · Staff: orders only'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->badge(),
                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageUsers::route('/')];
    }
}
