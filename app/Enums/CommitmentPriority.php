<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CommitmentPriority: string implements HasColor, HasIcon, HasLabel
{
    case Baja = 'baja';
    case Media = 'media';
    case Alta = 'alta';
    case Critica = 'critica';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baja => 'Baja',
            self::Media => 'Media',
            self::Alta => 'Alta',
            self::Critica => 'Crítica',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baja => 'gray',
            self::Media => 'info',
            self::Alta => 'warning',
            self::Critica => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Baja => 'heroicon-o-arrow-down',
            self::Media => 'heroicon-o-minus',
            self::Alta => 'heroicon-o-arrow-up',
            self::Critica => 'heroicon-o-exclamation-triangle',
        };
    }

    /**
     * Orden de severidad usado para "ordenar primero los criticos" en tablas.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Critica => 4,
            self::Alta => 3,
            self::Media => 2,
            self::Baja => 1,
        };
    }
}
