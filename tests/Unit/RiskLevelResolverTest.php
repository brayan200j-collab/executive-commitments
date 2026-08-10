<?php

namespace Tests\Unit;

use App\Enums\RiskImpact;
use App\Enums\RiskLevel;
use App\Enums\RiskProbability;
use App\Services\Risks\RiskLevelResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RiskLevelResolverTest extends TestCase
{
    #[DataProvider('matrix')]
    public function test_resolves_expected_level(RiskProbability $probability, RiskImpact $impact, RiskLevel $expected): void
    {
        $level = (new RiskLevelResolver)->resolve($probability, $impact);

        $this->assertSame($expected, $level);
    }

    public static function matrix(): array
    {
        return [
            'baja/bajo -> bajo' => [RiskProbability::Baja, RiskImpact::Bajo, RiskLevel::Bajo],
            'baja/medio -> bajo' => [RiskProbability::Baja, RiskImpact::Medio, RiskLevel::Bajo],
            'baja/alto -> medio' => [RiskProbability::Baja, RiskImpact::Alto, RiskLevel::Medio],
            'media/bajo -> bajo' => [RiskProbability::Media, RiskImpact::Bajo, RiskLevel::Bajo],
            'media/medio -> medio' => [RiskProbability::Media, RiskImpact::Medio, RiskLevel::Medio],
            'media/alto -> alto' => [RiskProbability::Media, RiskImpact::Alto, RiskLevel::Alto],
            'alta/bajo -> medio' => [RiskProbability::Alta, RiskImpact::Bajo, RiskLevel::Medio],
            'alta/medio -> alto' => [RiskProbability::Alta, RiskImpact::Medio, RiskLevel::Alto],
            // Regla obligatoria de la prueba tecnica: alta + alto = critico.
            'alta/alto -> critico' => [RiskProbability::Alta, RiskImpact::Alto, RiskLevel::Critico],
        ];
    }
}
