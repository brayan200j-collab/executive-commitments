<?php

namespace App\Models;

use App\Enums\MeetingStatus;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'organization',
        'responsible_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => MeetingStatus::class,
        ];
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function commitments(): HasMany
    {
        return $this->hasMany(Commitment::class);
    }
}
