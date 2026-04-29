<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use TracksSoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'cover_image',
        'category_id',
        'publisher',
        'place_of_publication',
        'edition',
        'published_year',
        'page_count',
        'stock_total',
        'stock_available',
        'status',
        'description',
        'delete',
        'deleted_by',
        'deleted_ip',
    ];

    protected function casts(): array
    {
        return [
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
