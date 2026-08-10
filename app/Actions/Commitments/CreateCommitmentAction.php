<?php

namespace App\Actions\Commitments;

use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * El codigo del compromiso lo asigna CommitmentObserver::creating() (unica
 * fuente de verdad, valida tambien para factories/seeders). Esta Action se
 * encarga de lo que SI es especifico del alta desde la app: dejar la
 * primera fila de auditoria (null -> estado inicial) dentro de la misma
 * transaccion que la creacion del registro.
 */
class CreateCommitmentAction
{
    public function __construct(
        private readonly RecordCommitmentStatusChangeAction $recordStatusChange,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data, ?User $actor = null): Commitment
    {
        return DB::transaction(function () use ($data, $actor): Commitment {
            $commitment = Commitment::create($data);

            $this->recordStatusChange->__invoke(
                commitment: $commitment,
                oldStatus: null,
                newStatus: $commitment->status instanceof CommitmentStatus
                    ? $commitment->status
                    : CommitmentStatus::from($commitment->status),
                actor: $actor,
            );

            return $commitment;
        });
    }
}
