<?php

namespace App\Filament\Widgets;

use App\Models\CommitmentStatusHistory;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivityWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return 'Última actividad';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => CommitmentStatusHistory::query()->with(['commitment', 'user'])->latest('created_at'))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('commitment.code')
                    ->label('Compromiso'),
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
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
