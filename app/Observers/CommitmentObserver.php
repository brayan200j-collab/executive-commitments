<?php

namespace App\Observers;

use App\Actions\Commitments\GenerateCommitmentCodeAction;
use App\Actions\Commitments\RecordCommitmentStatusChangeAction;
use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use Illuminate\Support\Facades\Auth;

/**
 * Regla obligatoria #3 de la prueba: al cambiar el estado de un compromiso
 * se registra automaticamente quien lo hizo, cuando, y el estado
 * anterior/nuevo. Se hace aqui (y no en el Resource de Filament) para que
 * CUALQUIER via de actualizacion (formulario, accion rapida en tabla,
 * tinker, un futuro job) quede auditada sin depender de que cada
 * desarrollador recuerde llamar a una Action.
 */
class CommitmentObserver
{
    public function __construct(
        private readonly GenerateCommitmentCodeAction $generateCode,
        private readonly RecordCommitmentStatusChangeAction $recordStatusChange,
    ) {}

    /**
     * El codigo se asigna aqui -no en la factory ni en el Resource- para
     * que cualquier via de creacion (Filament, seeder, factory en lote,
     * tinker) obtenga un codigo correlativo correcto. Hacerlo en un hook
     * "afterMaking" de la factory rompe con creaciones en lote: Laravel
     * arma primero N modelos en memoria (Factory::make x N) y recien
     * despues los persiste, asi que todos verian el mismo "ultimo codigo"
     * en BD y colisionarian. El evento `creating` en cambio se dispara
     * justo antes del INSERT de cada modelo, uno a la vez.
     */
    public function creating(Commitment $commitment): void
    {
        if (blank($commitment->code)) {
            $commitment->code = ($this->generateCode)();
        }
    }

    public function updating(Commitment $commitment): void
    {
        if (! $commitment->isDirty('status')) {
            return;
        }

        $oldStatus = CommitmentStatus::from($commitment->getRawOriginal('status'));
        $newStatus = $commitment->status instanceof CommitmentStatus
            ? $commitment->status
            : CommitmentStatus::from($commitment->status);

        $this->recordStatusChange->__invoke(
            commitment: $commitment,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            actor: Auth::user(),
        );
    }
}
