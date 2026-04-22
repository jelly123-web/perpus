<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use TracksSoftDeletes;

    protected $fillable = ['name', 'label', 'delete', 'deleted_by', 'deleted_ip'];

    protected function casts(): array
    {
        return [
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
