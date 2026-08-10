<?php

namespace App\Models;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use Carbon\Carbon;
use Database\Factories\CommitmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commitment extends Model
{
    /** @use HasFactory<CommitmentFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'meeting_id',
        'description',
        'responsible_id',
        'due_date',
        'priority',
        'status',
        'progress_percentage',
        'evidence',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'priority' => CommitmentPriority::class,
            'status' => CommitmentStatus::class,
            'progress_percentage' => 'integer',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CommitmentStatusHistory::class)->latest('created_at');
    }

    /**
     * Un compromiso vencido nunca se marca manualmente: se calcula siempre
     * a partir de la fecha limite y el estado actual (regla obligatoria #5
     * de la prueba tecnica). scopeOverdue() replica la misma condicion en SQL.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->status->countsAsOverdueEligible()
                && $this->due_date !== null
                && $this->due_date->lt(Carbon::today()),
        );
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', Carbon::today())
            ->where('status', '!=', CommitmentStatus::Cumplido->value);
    }

    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays($days)])
            ->where('status', '!=', CommitmentStatus::Cumplido->value);
    }
}
