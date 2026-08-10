<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RiskStatus: string implements HasColor, HasLabel
{
    case Activo = 'activo';
    case Mitigado = 'mitigado';
    case Cerrado = 'cerrado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Mitigado => 'Mitigado',
            self::Cerrado => 'Cerrado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Activo => 'danger',
            self::Mitigado => 'warning',
            self::Cerrado => 'gray',
        };
    }
}
