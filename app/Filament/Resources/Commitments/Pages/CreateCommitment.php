<?php

namespace App\Filament\Resources\Commitments\Pages;

use App\Actions\Commitments\CreateCommitmentAction;
use App\Filament\Resources\Commitments\CommitmentResource;
use App\Models\Commitment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCommitment extends CreateRecord
{
    protected static string $resource = CommitmentResource::class;

    /**
     * El código correlativo y la primera fila de auditoria se generan en
     * CreateCommitmentAction (unica fuente de verdad), no aqui.
     */
    protected function handleRecordCreation(array $data): Commitment
    {
        return app(CreateCommitmentAction::class)($data, Auth::user());
    }
}
