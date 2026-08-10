<?php

namespace Database\Factories;

use App\Enums\RiskImpact;
use App\Enums\RiskProbability;
use App\Enums\RiskStatus;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Risk>
 */
class RiskFactory extends Factory
{
    /**
     * Descripciones curadas a mano: Faker::sentence()/text() generan
     * "Lorem ipsum" (latin falso, no depende del locale), no texto real
     * en español.
     *
     * @var array<int, string>
     */
    private const DESCRIPTIONS = [
        'Posible retraso en la entrega de insumos por parte del proveedor principal',
        'Riesgo de fuga de información sensible por falta de controles de acceso',
        'Dependencia crítica de un único desarrollador para el módulo de pagos',
        'Posible incumplimiento normativo por cambios en la regulación fiscal',
        'Riesgo cambiario por operaciones en moneda extranjera',
        'Rotación alta de personal clave en el área de tecnología',
        'Falta de plan de contingencia ante caída del sistema principal',
        'Posible sobrecosto en el proyecto por cambios de alcance no controlados',
        'Riesgo reputacional por quejas recurrentes de clientes',
        'Vulnerabilidad de seguridad detectada en el servidor de producción',
        'Retraso en la obtención de permisos regulatorios',
        'Dependencia de un solo proveedor para componentes críticos',
    ];

    public function definition(): array
    {
        return [
            'description' => $this->faker->randomElement(self::DESCRIPTIONS),
            'probability' => $this->faker->randomElement(RiskProbability::cases())->value,
            'impact' => $this->faker->randomElement(RiskImpact::cases())->value,
            // 'code' lo asigna RiskObserver::creating(), 'level' lo calcula RiskObserver::saving().
            'responsible_id' => User::factory(),
            'status' => $this->faker->randomElement(RiskStatus::cases())->value,
        ];
    }

    public function critical(): static
    {
        return $this->state(fn () => [
            'probability' => RiskProbability::Alta->value,
            'impact' => RiskImpact::Alto->value,
            'status' => RiskStatus::Activo->value,
        ]);
    }
}
