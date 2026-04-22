<?php

namespace App\Models;

use App\Models\Concerns\TracksSoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Throwable;

class Setting extends Model
{
    use TracksSoftDeletes;

    protected $fillable = ['key', 'label', 'type', 'value', 'delete', 'deleted_by', 'deleted_ip'];

    protected function casts(): array
    {
        return [
            'delete' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    protected static array $resolvedValues = [];

    protected static function booted(): void
    {
        static::saved(function () {
            static::$resolvedValues = [];
        });

        static::deleted(function () {
            static::$resolvedValues = [];
        });
    }

    public static function valueOr(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$resolvedValues)) {
            return static::$resolvedValues[$key];
        }

        try {
            return static::$resolvedValues[$key] = static::query()
                ->where('key', $key)
                ->value('value') ?? $default;
        } catch (QueryException|Throwable $exception) {
            return static::$resolvedValues[$key] = $default;
        }
    }

    public static function appLogoPath(): ?string
    {
        $logo = static::valueOr('app_logo');

        if (is_string($logo) && $logo !== '' && File::exists(public_path($logo))) {
            return $logo;
        }

        return collect(File::glob(public_path('branding/app-logo-*')))
            ->filter(fn (string $path) => File::isFile($path))
            ->sortDesc()
            ->map(fn (string $path) => 'branding/'.basename($path))
            ->first();
    }
}
