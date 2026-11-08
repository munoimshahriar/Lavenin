<?php

namespace App\Enums;

use App\Models\Setting;
use Filament\Support\Contracts\HasLabel;

/**
 * Payment methods available at checkout. Manual "Send Money" methods need the customer's sender
 * number and TrxID, which an admin verifies. A gateway (e.g. SSLCommerz) would be added as a new case
 * with requiresTransaction() = false and its own redirect flow.
 */
enum PaymentMethod: string implements HasLabel
{
    case Cod = 'cod';
    case Bkash = 'bkash';
    case Nagad = 'nagad';

    public function getLabel(): string
    {
        return __('shop.payment.'.$this->value);
    }

    public function requiresTransaction(): bool
    {
        return $this !== self::Cod;
    }

    public function merchantNumber(): ?string
    {
        return match ($this) {
            self::Bkash => Setting::read('bkash_number') ?: null,
            self::Nagad => Setting::read('nagad_number') ?: null,
            self::Cod => null,
        };
    }

    /** Methods the customer can pick right now (manual methods need a merchant number in Settings). */
    public static function available(): array
    {
        return array_values(array_filter(self::cases(), fn (self $m) => ! $m->requiresTransaction() || $m->merchantNumber()));
    }

    public function initialPaymentStatus(): PaymentStatus
    {
        return $this->requiresTransaction() ? PaymentStatus::PendingVerification : PaymentStatus::Unpaid;
    }
}
