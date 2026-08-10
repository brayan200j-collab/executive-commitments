<?php

namespace App\Filament\Resources\Commitments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Panel de solo lectura: la auditoria (regla obligatoria #3) se escribe
 * unicamente via CommitmentObserver + RecordCommitmentStatusChangeAction,
 * nunca a mano desde la UI. Por eso este relation manager no expone
 * crear/editar/eliminar.
 */
class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Historial de auditoría';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('new_status')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario'),
                TextColumn::make('old_status')
                    ->label('Estado anterior')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('new_status')
                    ->label('Estado nuevo')
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
