<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Unpaid = 'unpaid';
    case PendingVerification = 'pending_verification';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return __('shop.payment_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Unpaid => 'gray',
            self::PendingVerification => 'warning',
            self::Paid => 'success',
            self::Refunded => 'danger',
        };
    }
}
