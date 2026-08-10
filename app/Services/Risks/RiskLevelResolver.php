<?php

namespace App\Services\Risks;

use App\Enums\RiskImpact;
use App\Enums\RiskLevel;
use App\Enums\RiskProbability;

/**
 * Unica fuente de verdad para la matriz probabilidad x impacto.
 *
 * Regla obligatoria de la prueba: probabilidad alta + impacto alto = critico.
 * El resto de la matriz sigue la convencion estandar de 3x3 (ver DECISIONS.md),
 * derivada del producto de los pesos de cada enum:
 *
 *            Bajo(1)  Medio(2)  Alto(3)
 *   Baja(1)    Bajo     Bajo     Medio
 *   Media(2)   Bajo     Medio    Alto
 *   Alta(3)    Medio    Alto     Critico
 */
class RiskLevelResolver
{
    public function resolve(RiskProbability $probability, RiskImpact $impact): RiskLevel
    {
        $score = $probability->weight() * $impact->weight();

        return match (true) {
            $score >= 9 => RiskLevel::Critico,
            $score >= 6 => RiskLevel::Alto,
            $score >= 3 => RiskLevel::Medio,
            default => RiskLevel::Bajo,
        };
    }
}
