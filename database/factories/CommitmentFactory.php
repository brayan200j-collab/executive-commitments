<?php

namespace Database\Factories;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commitment>
 */
class CommitmentFactory extends Factory
{
    /**
     * Descripciones curadas a mano: Faker::sentence()/text() generan
     * "Lorem ipsum" (latin falso, no depende del locale), no texto real
     * en español.
     *
     * @var array<int, string>
     */
    private const DESCRIPTIONS = [
        'Entregar el informe financiero del trimestre al comité directivo',
        'Actualizar la documentación técnica del módulo de facturación',
        'Implementar el nuevo flujo de aprobación de compras',
        'Migrar la base de datos de clientes al nuevo servidor',
        'Capacitar al equipo comercial en el uso del nuevo CRM',
        'Revisar y firmar el contrato con el proveedor logístico',
        'Publicar el reporte de indicadores de gestión mensual',
        'Coordinar la auditoría interna de procesos de calidad',
        'Definir el plan de contingencia ante fallas del sistema',
        'Actualizar las políticas de seguridad de la información',
        'Realizar el cierre contable del mes anterior',
        'Enviar la propuesta comercial al cliente potencial',
        'Ajustar el cronograma del proyecto según los nuevos plazos',
        'Validar los indicadores de satisfacción del cliente',
        'Preparar la presentación para la junta directiva',
    ];

    /**
     * @var array<int, string>
     */
    private const EVIDENCE_NOTES = [
        'Se adjunta acta de la reunión con los acuerdos firmados.',
        'Evidencia enviada por correo al responsable del área.',
        'Documento cargado en el repositorio compartido del proyecto.',
        'Pendiente de validación final por parte del cliente.',
        'Aprobado por el comité en la última sesión de seguimiento.',
    ];

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'description' => $this->faker->randomElement(self::DESCRIPTIONS),
            'responsible_id' => User::factory(),
            'due_date' => $this->faker->dateTimeBetween('-3 weeks', '+6 weeks'),
            'priority' => $this->faker->randomElement(CommitmentPriority::cases())->value,
            'status' => $this->faker->randomElement(CommitmentStatus::cases())->value,
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'evidence' => $this->faker->boolean(40) ? $this->faker->randomElement(self::EVIDENCE_NOTES) : null,
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => $this->faker->dateTimeBetween('-4 weeks', '-1 days'),
            'status' => $this->faker->randomElement([
                CommitmentStatus::Pendiente,
                CommitmentStatus::EnProgreso,
                CommitmentStatus::Bloqueado,
            ])->value,
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn () => [
            'due_date' => $this->faker->dateTimeBetween('now', '+6 days'),
            'status' => $this->faker->randomElement([
                CommitmentStatus::Pendiente,
                CommitmentStatus::EnProgreso,
            ])->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => CommitmentStatus::Cumplido->value,
            'progress_percentage' => 100,
        ]);
    }
}
