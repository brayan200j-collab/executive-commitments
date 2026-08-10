<?php

namespace App\Filament\Resources\Meetings\Tables;

use App\Enums\MeetingStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('organization')
                    ->label('Organización')
                    ->searchable(),
                TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('commitments_count')
                    ->label('Compromisos')
                    ->counts('commitments')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(MeetingStatus::class),
            ])
            ->defaultSort('date', 'desc')
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
