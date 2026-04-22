<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

trait TracksSoftDeletes
{
    use SoftDeletes;

    protected static function bootTracksSoftDeletes(): void
    {
        static::deleting(function ($model): void {
            if (property_exists($model, 'forceDeleting') && $model->isForceDeleting()) {
                return;
            }

            $model->forceFill([
                'delete' => true,
                'deleted_by' => auth()->id(),
                'deleted_ip' => request()->ip(),
            ])->saveQuietly();
        });

        static::restoring(function ($model): void {
            $model->forceFill([
                'delete' => false,
                'deleted_by' => null,
                'deleted_ip' => null,
            ]);
        });
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }
}
