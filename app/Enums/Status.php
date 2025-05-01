<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasLabel
{
    case Confirm = 'confirm';
    case Arrived = 'arrived';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return $this->name;

        // or

        return match ($this) {
            self::Confirm => 'confirm',
            self::Arrived => 'arrived',
            self::Cancelled => 'cancelled',
        };
    }
}
