<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Order extends Model
{
    /** Orders are only ever created server-side by CheckoutService; nothing comes straight from a request. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'stock_deducted' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'LV'.now()->format('ymd').strtoupper(Str::random(4));
        } while (static::where('number', $number)->exists());

        return $number;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Move the order to a new status. Stock is taken when an order is confirmed and given back if a
     * confirmed order is cancelled.
     */
    public function transitionTo(OrderStatus $to): void
    {
        if (! $this->status->canTransitionTo($to)) {
            throw new InvalidArgumentException("Cannot move order from {$this->status->value} to {$to->value}.");
        }

        DB::transaction(function () use ($to) {
            $this->loadMissing('items.product');

            if ($to === OrderStatus::Confirmed && ! $this->stock_deducted) {
                foreach ($this->items as $item) {
                    $item->product?->decrement('stock', $item->quantity);
                }
                $this->stock_deducted = true;
            }

            if ($to === OrderStatus::Cancelled && $this->stock_deducted) {
                foreach ($this->items as $item) {
                    $item->product?->increment('stock', $item->quantity);
                }
                $this->stock_deducted = false;
            }

            if ($to === OrderStatus::Delivered && $this->payment_method === PaymentMethod::Cod) {
                $this->payment_status = PaymentStatus::Paid;
            }

            $this->status = $to;
            $this->save();
        });
    }
}
