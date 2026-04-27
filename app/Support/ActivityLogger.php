<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

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

        // Kirim notifikasi ke Discord setelah response utama selesai agar request utama tidak ikut menunggu webhook.
        $isAsyncRequest = Request::expectsJson() || Request::ajax();

        if (! $isAsyncRequest && in_array($action, ['create', 'update', 'delete', 'restore', 'login', 'import', 'export', 'backup'])) {
            app()->terminating(function () use ($module, $action, $description): void {
                DiscordNotifier::notifyAction($module, $action, $description);
            });
        }
    }
}
