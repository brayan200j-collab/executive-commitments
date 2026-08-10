<?php

namespace App\Actions\Commitments;

use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\CommitmentStatusHistory;
use App\Models\User;

/**
 * Unico punto donde se escribe una fila de auditoria de compromisos
 * (auditoria minima obligatoria: quien, cuando, estado anterior y nuevo).
 */
class RecordCommitmentStatusChangeAction
{
    public function __invoke(
        Commitment $commitment,
        ?CommitmentStatus $oldStatus,
        CommitmentStatus $newStatus,
        ?User $actor,
    ): CommitmentStatusHistory {
        return CommitmentStatusHistory::create([
            'commitment_id' => $commitment->id,
            'user_id' => $actor?->id ?? $commitment->responsible_id,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus->value,
        ]);
    }
}
