<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RiskProbability: string implements HasColor, HasLabel
{
    case Baja = 'baja';
    case Media = 'media';
    case Alta = 'alta';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baja => 'Baja',
            self::Media => 'Media',
            self::Alta => 'Alta',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baja => 'success',
            self::Media => 'warning',
            self::Alta => 'danger',
        };
    }

    /**
     * Peso numerico usado por RiskLevelResolver para construir la matriz.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Baja => 1,
            self::Media => 2,
            self::Alta => 3,
        };
    }
}
