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

        // Kirim notifikasi ke Discord untuk aksi penting
        if (in_array($action, ['create', 'update', 'delete', 'restore', 'login', 'logout', 'import', 'export', 'backup'])) {
            DiscordNotifier::notifyAction($module, $action, $description);
        }
    }
}
