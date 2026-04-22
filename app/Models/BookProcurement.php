<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookProcurement extends Model
{
    use TracksSoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'published_year',
        'quantity',
        'notes',
        'category_id',
        'status',
        'proposed_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'delete',
        'deleted_by',
        'deleted_ip',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
