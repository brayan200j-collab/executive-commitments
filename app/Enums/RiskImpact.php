<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RiskImpact: string implements HasColor, HasLabel
{
    case Bajo = 'bajo';
    case Medio = 'medio';
    case Alto = 'alto';

    public function getLabel(): string
    {
        return match ($this) {
            self::Bajo => 'Bajo',
            self::Medio => 'Medio',
            self::Alto => 'Alto',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Bajo => 'success',
            self::Medio => 'warning',
            self::Alto => 'danger',
        };
    }

    /**
     * Peso numerico usado por RiskLevelResolver para construir la matriz.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Bajo => 1,
            self::Medio => 2,
            self::Alto => 3,
        };
    }
}
