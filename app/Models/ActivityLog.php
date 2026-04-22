<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use TracksSoftDeletes;

    protected $fillable = ['user_id', 'module', 'action', 'description', 'properties', 'delete', 'deleted_by', 'deleted_ip'];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
