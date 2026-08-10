<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RiskLevel: string implements HasColor, HasIcon, HasLabel
{
    case Bajo = 'bajo';
    case Medio = 'medio';
    case Alto = 'alto';
    case Critico = 'critico';

    public function getLabel(): string
    {
        return match ($this) {
            self::Bajo => 'Bajo',
            self::Medio => 'Medio',
            self::Alto => 'Alto',
            self::Critico => 'Crítico',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Bajo => 'success',
            self::Medio => 'warning',
            self::Alto => 'danger',
            self::Critico => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Bajo => 'heroicon-o-shield-check',
            self::Medio => 'heroicon-o-shield-exclamation',
            self::Alto => 'heroicon-o-exclamation-triangle',
            self::Critico => 'heroicon-o-fire',
        };
    }
}
