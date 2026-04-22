<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use TracksSoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'delete', 'deleted_by', 'deleted_ip'];

    protected function casts(): array
    {
        return [
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
