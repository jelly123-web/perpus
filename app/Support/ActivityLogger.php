<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $module, string $action, string $description, array $properties = []): void
    {
        ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
