<?php

namespace App\Actions\Commitments;

use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCommitmentAction
{
    public function __construct(
        private readonly GenerateCommitmentCodeAction $generateCode,
        private readonly RecordCommitmentStatusChangeAction $recordStatusChange,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data, ?User $actor = null): Commitment
    {
        return DB::transaction(function () use ($data, $actor): Commitment {
            $commitment = Commitment::create([
                ...$data,
                'code' => ($this->generateCode)(),
            ]);

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
