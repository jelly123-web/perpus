<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanction extends Model
{
    use TracksSoftDeletes;

    protected $fillable = [
        'loan_id',
        'member_id',
        'processed_by',
        'type',
        'status',
        'reason',
        'duration_days',
        'starts_at',
        'ends_at',
        'notes',
        'delete',
        'deleted_by',
        'deleted_ip',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
