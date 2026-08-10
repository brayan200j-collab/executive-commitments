<?php

namespace App\Observers;

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
        private readonly RecordCommitmentStatusChangeAction $recordStatusChange,
    ) {}

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
