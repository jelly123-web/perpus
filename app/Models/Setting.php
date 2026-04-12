<?php

namespace App\Models;

use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Setting extends Model
{
    protected $fillable = ['key', 'label', 'type', 'value'];

    protected static array $resolvedValues = [];

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
}
