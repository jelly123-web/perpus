<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Setting;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    use HandlesAsyncRequests;

    public function index(): View
    {
        $settings = collect([
            'app_name' => [
                'key' => 'app_name',
                'label' => 'Nama Aplikasi',
                'type' => 'text',
                'value' => 'LibraVault',
            ],
            'app_logo' => [
                'key' => 'app_logo',
                'label' => 'Logo Aplikasi',
                'type' => 'file',
                'value' => null,
            ],
            'app_color' => [
                'key' => 'app_color',
                'label' => 'Warna Utama',
                'type' => 'color',
                'value' => '#c4956a',
            ],
        ])->map(function (array $setting): Setting {
            return Setting::query()->firstOrCreate(
                ['key' => $setting['key']],
                [
                    'label' => $setting['label'],
                    'type' => $setting['type'],
                    'value' => $setting['value'],
                ]
            );
        });

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        Setting::query()->updateOrCreate(
            ['key' => 'app_name'],
            ['label' => 'Nama Aplikasi', 'type' => 'text', 'value' => trim($data['app_name'])]
        );

        Setting::query()->updateOrCreate(
            ['key' => 'app_color'],
            ['label' => 'Warna Utama', 'type' => 'color', 'value' => strtoupper($data['app_color'])]
        );

        if ($request->hasFile('app_logo')) {
            $logoSetting = Setting::query()->where('key', 'app_logo')->first();
            $oldLogoPath = $logoSetting?->value ? public_path($logoSetting->value) : null;

            $file = $request->file('app_logo');
            $fileName = 'app-logo-'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $targetDirectory = public_path('branding');

            if (! is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            $file->move($targetDirectory, $fileName);
            
            Setting::query()->updateOrCreate(
                ['key' => 'app_logo'],
                ['label' => 'Logo Aplikasi', 'type' => 'file', 'value' => 'branding/'.$fileName]
            );

            if ($oldLogoPath && is_file($oldLogoPath)) {
                @unlink($oldLogoPath);
            }
        } elseif ($request->input('remove_logo') === '1') {
            $logoSetting = Setting::query()->where('key', 'app_logo')->first();
            $oldLogoPath = $logoSetting?->value ? public_path($logoSetting->value) : null;

            if ($oldLogoPath && is_file($oldLogoPath)) {
                @unlink($oldLogoPath);
            }

            $logoSetting?->update(['value' => null]);
        }

        ActivityLogger::log('settings', 'update', 'Memperbarui konfigurasi aplikasi');

        return $this->successResponse($request, 'Pengaturan berhasil disimpan.');
    }
}
