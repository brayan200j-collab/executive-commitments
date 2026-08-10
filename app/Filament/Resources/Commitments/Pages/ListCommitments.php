<?php

namespace App\Filament\Resources\Commitments\Pages;

use App\Filament\Resources\Commitments\CommitmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommitments extends ListRecords
{
    protected static string $resource = CommitmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
