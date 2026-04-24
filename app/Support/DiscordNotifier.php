<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordNotifier
{
    public static function send(string $title, string $description, string $color = '3447003'): void
    {
        $webhookUrl = Setting::valueOr('discord_webhook_url');

        if (! $webhookUrl || ! str_starts_with($webhookUrl, 'https://discord.com/api/webhooks/')) {
            return;
        }

        $user = Auth::user();
        $appName = Setting::valueOr('app_name', 'Perpus Digital');

        try {
            Http::timeout(3)
                ->connectTimeout(2)
                ->post($webhookUrl, [
                'embeds' => [
                    [
                        'title' => '🔔 ' . $title,
                        'description' => $description,
                        'color' => $color,
                        'fields' => [
                            [
                                'name' => 'Aktor',
                                'value' => $user ? "{$user->name} ({$user->role?->label})" : 'Sistem',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Waktu',
                                'value' => now()->translatedFormat('d F Y H:i:s'),
                                'inline' => true,
                            ],
                        ],
                        'footer' => [
                            'text' => "Log Transaksi | {$appName}",
                        ],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi Discord: ' . $e->getMessage());
        }
    }

    public static function notifyAction(string $module, string $action, string $description): void
    {
        $colors = [
            'create' => '3066993', // Green
            'update' => '15105570', // Orange/Yellow
            'delete' => '15158332', // Red
            'restore' => '3447003', // Blue
            'login' => '1752220', // Aqua/Cyan
            'logout' => '9807270', // Grey
        ];

        $actionLabels = [
            'create' => 'Data Baru',
            'update' => 'Perubahan Data',
            'delete' => 'Penghapusan Data',
            'restore' => 'Pemulihan Data',
            'login' => 'Sesi Masuk',
            'logout' => 'Sesi Keluar',
            'import' => 'Import Data',
            'export' => 'Export Data',
            'backup' => 'Pembuatan Backup',
        ];

        $moduleLabels = [
            'books' => 'Buku',
            'users' => 'Pengguna',
            'loans' => 'Peminjaman',
            'categories' => 'Kategori',
            'settings' => 'Pengaturan',
            'roles' => 'Akses/Role',
            'backups' => 'Backup & Restore',
            'auth' => 'Otentikasi',
            'restore' => 'Tempat Sampah',
        ];

        $title = ($actionLabels[$action] ?? ucfirst($action)) . ': ' . ($moduleLabels[$module] ?? ucfirst($module));
        $color = $colors[$action] ?? '3447003';

        self::send($title, $description, $color);
    }
}
