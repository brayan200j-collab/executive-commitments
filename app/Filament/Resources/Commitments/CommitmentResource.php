<?php

namespace App\Filament\Resources\Commitments;

use App\Filament\Resources\Commitments\Pages\CreateCommitment;
use App\Filament\Resources\Commitments\Pages\EditCommitment;
use App\Filament\Resources\Commitments\Pages\ListCommitments;
use App\Filament\Resources\Commitments\RelationManagers\StatusHistoriesRelationManager;
use App\Filament\Resources\Commitments\Schemas\CommitmentForm;
use App\Filament\Resources\Commitments\Tables\CommitmentsTable;
use App\Models\Commitment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommitmentResource extends Resource
{
    protected static ?string $model = Commitment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?string $navigationLabel = 'Compromisos';

    protected static ?string $modelLabel = 'compromiso';

    protected static ?string $pluralModelLabel = 'compromisos';

    protected static string|UnitEnum|null $navigationGroup = 'Seguimiento';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return CommitmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommitmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StatusHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommitments::route('/'),
            'create' => CreateCommitment::route('/create'),
            'edit' => EditCommitment::route('/{record}/edit'),
        ];
    }
}
