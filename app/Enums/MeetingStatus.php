<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MeetingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Programada = 'programada';
    case Realizada = 'realizada';
    case Cancelada = 'cancelada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Programada => 'Programada',
            self::Realizada => 'Realizada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Programada => 'info',
            self::Realizada => 'success',
            self::Cancelada => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Programada => 'heroicon-o-calendar',
            self::Realizada => 'heroicon-o-check-circle',
            self::Cancelada => 'heroicon-o-x-circle',
        };
    }
}
