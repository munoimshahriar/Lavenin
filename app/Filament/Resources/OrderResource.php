<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    protected static array $viewRoles = ['owner', 'manager', 'staff'];

    // Orders are never created or edited directly in admin; only status/payment actions.
    protected static array $editRoles = [];

    protected static array $deleteRoles = ['owner', 'manager'];

    public static function getNavigationBadge(): ?string
    {
        $n = Order::where('status', OrderStatus::Pending)->count();

        return $n ? (string) $n : null;
    }

    /** @param class-string<FilamentTablesActionsAction|FilamentActionsAction> $action */
    public static function statusActions(string $action = Action::class): array
    {
        $actions = [];
        foreach ([OrderStatus::Confirmed, OrderStatus::Packed, OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Cancelled] as $to) {
            $actions[] = $action::make('to_'.$to->value)
                ->label('Mark '.$to->getLabel())
                ->color($to->getColor())
                ->icon($to === OrderStatus::Cancelled ? 'heroicon-o-x-circle' : 'heroicon-o-arrow-right-circle')
                ->visible(fn (Order $record) => $record->status->canTransitionTo($to))
                ->requiresConfirmation($to === OrderStatus::Cancelled)
                ->action(function (Order $record) use ($to) {
                    $record->transitionTo($to);
                    Notification::make()->title("Order {$record->number} → {$to->getLabel()}")->success()->send();
                });
        }

        $actions[] = $action::make('verify_payment')
            ->label('Verify payment')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn (Order $record) => $record->payment_status === PaymentStatus::PendingVerification)
            ->requiresConfirmation()
            ->modalDescription(fn (Order $record) => "Confirm you received ৳{$record->total} via {$record->payment_method->getLabel()} from {$record->sender_number}, TrxID {$record->trx_id}.")
            ->action(function (Order $record) {
                $record->update(['payment_status' => PaymentStatus::Paid]);
                Notification::make()->title('Payment marked as paid')->success()->send();
            });

        $actions[] = $action::make('reject_payment')
            ->label('Payment not found')
            ->icon('heroicon-o-exclamation-triangle')
            ->color('danger')
            ->visible(fn (Order $record) => $record->payment_status === PaymentStatus::PendingVerification)
            ->requiresConfirmation()
            ->action(fn (Order $record) => $record->update(['payment_status' => PaymentStatus::Unpaid]));

        $actions[] = $action::make('slip')
            ->label('Packing slip')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(fn (Order $record) => route('admin.orders.slip', $record))
            ->openUrlInNewTab();

        return $actions;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y, h:i A')->sortable()->label('Date'),
                Tables\Columns\TextColumn::make('name')->searchable()->description(fn (Order $r) => $r->phone),
                Tables\Columns\TextColumn::make('phone')->searchable()->hidden(),
                Tables\Columns\TextColumn::make('district'),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state) => '৳'.number_format($state))->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('payment_status')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(OrderStatus::class)->multiple(),
                Tables\Filters\SelectFilter::make('payment_status')->options(PaymentStatus::class),
                Tables\Filters\SelectFilter::make('payment_method')->options(PaymentMethod::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                ActionGroup::make(static::statusActions()),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Order')->schema([
                Infolists\Components\TextEntry::make('number'),
                Infolists\Components\TextEntry::make('created_at')->dateTime('d M Y, h:i A'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('payment_method')->badge()->color('gray'),
                Infolists\Components\TextEntry::make('payment_status')->badge(),
                Infolists\Components\TextEntry::make('sender_number')->placeholder('—'),
                Infolists\Components\TextEntry::make('trx_id')->label('TrxID')->placeholder('—')->copyable(),
                Infolists\Components\TextEntry::make('coupon.code')->label('Coupon')->placeholder('—'),
            ])->columns(4),
            Infolists\Components\Section::make('Customer')->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('phone')->copyable(),
                Infolists\Components\TextEntry::make('email')->placeholder('—'),
                Infolists\Components\TextEntry::make('district'),
                Infolists\Components\TextEntry::make('city')->label('City / area'),
                Infolists\Components\TextEntry::make('address')->columnSpan(2),
                Infolists\Components\TextEntry::make('note')->placeholder('—')->columnSpanFull(),
            ])->columns(3),
            Infolists\Components\Section::make('Items')->schema([
                Infolists\Components\RepeatableEntry::make('items')->hiddenLabel()->schema([
                    Infolists\Components\TextEntry::make('name')->hiddenLabel(),
                    Infolists\Components\TextEntry::make('quantity')->hiddenLabel()->prefix('× '),
                    Infolists\Components\TextEntry::make('price')->hiddenLabel()->formatStateUsing(fn ($state) => '৳'.number_format($state)),
                ])->columns(3),
                Infolists\Components\TextEntry::make('subtotal')->formatStateUsing(fn ($state) => '৳'.number_format($state))->inlineLabel(),
                Infolists\Components\TextEntry::make('discount')->formatStateUsing(fn ($state) => '− ৳'.number_format($state))->inlineLabel(),
                Infolists\Components\TextEntry::make('shipping')->formatStateUsing(fn ($state) => '৳'.number_format($state))->inlineLabel(),
                Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => '৳'.number_format($state))->inlineLabel()->weight('bold'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
