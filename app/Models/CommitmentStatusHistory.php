<?php

namespace App\Models;

use App\Enums\CommitmentStatus;
use Database\Factories\CommitmentStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitmentStatusHistory extends Model
{
    /** @use HasFactory<CommitmentStatusHistoryFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'commitment_id',
        'user_id',
        'old_status',
        'new_status',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => CommitmentStatus::class,
            'new_status' => CommitmentStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function commitment(): BelongsTo
    {
        return $this->belongsTo(Commitment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
