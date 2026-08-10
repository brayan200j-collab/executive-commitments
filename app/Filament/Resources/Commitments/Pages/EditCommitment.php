<?php

namespace App\Filament\Resources\Commitments\Pages;

use App\Filament\Resources\Commitments\CommitmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommitment extends EditRecord
{
    protected static string $resource = CommitmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
