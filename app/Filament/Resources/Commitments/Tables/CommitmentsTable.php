<?php

namespace App\Filament\Resources\Commitments\Tables;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommitmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('meeting.title')
                    ->label('Reunión de origen')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->searchable(),
                TextColumn::make('due_date')
                    ->label('Fecha límite')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('is_overdue')
                    ->label('Vencido')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('progress_percentage')
                    ->label('Avance')
                    ->suffix('%')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(CommitmentStatus::class),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(CommitmentPriority::class),
                Filter::make('overdue')
                    ->label('Solo vencidos')
                    ->query(fn ($query) => $query->overdue()),
                Filter::make('due_soon')
                    ->label('Próximos a vencer (7 días)')
                    ->query(fn ($query) => $query->dueSoon()),
            ])
            ->defaultSort('due_date')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
