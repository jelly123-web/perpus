<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    use TracksSoftDeletes;

    protected $fillable = ['file_name', 'file_path', 'size_bytes', 'created_by', 'delete', 'deleted_by', 'deleted_ip'];

    protected function casts(): array
    {
        return [
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
