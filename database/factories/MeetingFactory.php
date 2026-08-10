<?php

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * Organizaciones ficticias con nombre profesional (no el generador
     * "company()" de Faker, que produce nombres sin sentido en español).
     *
     * @var array<int, string>
     */
    private const ORGANIZATIONS = [
        'Grupo Andino de Inversiones',
        'Constructora del Pacífico SAS',
        'Distribuidora Nacional de Alimentos',
        'TecnoSoluciones Andinas SAS',
        'Laboratorios Cordillera',
        'Textiles del Caribe SAS',
        'Logística Integral de Colombia',
        'Consultores Financieros Meridiano',
        'Energía Renovable del Sur SAS',
        'Comercializadora Andina de Café',
    ];

    /**
     * @var array<int, string>
     */
    private const TITLES = [
        'Comité directivo de seguimiento',
        'Reunión de planeación estratégica',
        'Kickoff del proyecto de transformación digital',
        'Comité de riesgos y cumplimiento',
        'Reunión de cierre de sprint',
        'Comité de seguimiento presupuestal',
        'Mesa de trabajo · mejora de procesos',
        'Reunión de seguimiento con el cliente',
        'Comité ejecutivo mensual',
        'Reunión de arranque de fase 2',
    ];

    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement(self::TITLES),
            // Fechas recientes (ultimas 3 semanas a proxima semana), no
            // "muy antiguas": una reunion de seguimiento ejecutivo pierde
            // sentido si es de hace meses.
            'date' => $this->faker->dateTimeBetween('-3 weeks', '+1 week'),
            'organization' => $this->faker->randomElement(self::ORGANIZATIONS),
            'responsible_id' => User::factory(),
            'status' => $this->faker->randomElement(MeetingStatus::cases())->value,
        ];
    }
}
