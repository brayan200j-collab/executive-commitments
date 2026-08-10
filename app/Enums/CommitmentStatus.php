<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CommitmentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pendiente = 'pendiente';
    case EnProgreso = 'en_progreso';
    case Bloqueado = 'bloqueado';
    case Cumplido = 'cumplido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnProgreso => 'En progreso',
            self::Bloqueado => 'Bloqueado',
            self::Cumplido => 'Cumplido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::EnProgreso => 'info',
            self::Bloqueado => 'danger',
            self::Cumplido => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pendiente => 'heroicon-o-clock',
            self::EnProgreso => 'heroicon-o-arrow-path',
            self::Bloqueado => 'heroicon-o-no-symbol',
            self::Cumplido => 'heroicon-o-check-circle',
        };
    }

    /**
     * Un compromiso cumplido nunca puede considerarse vencido,
     * sin importar que su fecha limite ya haya pasado.
     */
    public function countsAsOverdueEligible(): bool
    {
        return $this !== self::Cumplido;
    }
}
