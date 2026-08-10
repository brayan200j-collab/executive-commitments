<?php

namespace App\Models;

use App\Enums\RiskImpact;
use App\Enums\RiskLevel;
use App\Enums\RiskProbability;
use App\Enums\RiskStatus;
use Database\Factories\RiskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Risk extends Model
{
    /** @use HasFactory<RiskFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'probability',
        'impact',
        'level',
        'responsible_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'probability' => RiskProbability::class,
            'impact' => RiskImpact::class,
            'level' => RiskLevel::class,
            'status' => RiskStatus::class,
        ];
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
