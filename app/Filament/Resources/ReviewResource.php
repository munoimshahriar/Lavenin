<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ReviewResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    protected static array $viewRoles = ['owner', 'manager'];

    protected static array $deleteRoles = ['owner', 'manager'];

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Review::where('is_approved', false)->count();

        return $n ? (string) $n : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'product']))
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product')->limit(30),
                Tables\Columns\TextColumn::make('user.name')->label('Customer'),
                Tables\Columns\TextColumn::make('rating')->formatStateUsing(fn ($state) => str_repeat('★', $state).str_repeat('☆', 5 - $state))->color('warning'),
                Tables\Columns\TextColumn::make('body')->limit(60)->wrap(),
                Tables\Columns\IconColumn::make('is_approved')->boolean()->label('Approved'),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_approved')->label('Approved')])
            ->actions([
                Tables\Actions\Action::make('approve')->icon('heroicon-o-check')->color('success')
                    ->visible(fn (Review $r) => ! $r->is_approved)
                    ->action(fn (Review $r) => $r->update(['is_approved' => true])),
                Tables\Actions\Action::make('hide')->icon('heroicon-o-eye-slash')->color('gray')
                    ->visible(fn (Review $r) => $r->is_approved)
                    ->action(fn (Review $r) => $r->update(['is_approved' => false])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve')->icon('heroicon-o-check')
                    ->action(fn (Collection $records) => $records->each->update(['is_approved' => true])),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageReviews::route('/')];
    }
}
