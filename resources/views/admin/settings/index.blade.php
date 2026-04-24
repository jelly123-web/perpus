@extends('layouts.admin')

@section('content')
@php($title = 'Setting')
@php($eyebrow = 'Khusus Superadmin')
@php($appName = $settings['app_name']->value ?? 'LibraVault')
@php($appLogo = \App\Models\Setting::appLogoPath())
@php($showAppName = ($settings['show_app_name']->value ?? '1') === '1')

<style>
    .setting-page{display:flex;flex-direction:column;gap:24px}
    .setting-shell{display:grid;grid-template-columns:minmax(340px,460px) minmax(0,1fr);gap:20px}
    .setting-card{background:var(--bg-card);border:1px solid var(--border);border-radius:22px;box-shadow:var(--shadow-sm)}
    .setting-card-main{padding:24px}
    .setting-title{font-family:'Playfair Display',serif;font-size:32px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
    .setting-sub{font-size:14px;color:var(--muted);margin-top:8px;line-height:1.7}
    .setting-preview{padding:24px;position:relative;overflow:hidden}
    .setting-preview:before{content:'';position:absolute;right:-60px;top:-60px;width:180px;height:180px;border-radius:999px;background:radial-gradient(circle,rgba(196,149,106,.10),transparent 70%)}
    .setting-preview > *{position:relative;z-index:1}
    .setting-preview-shell{border-radius:26px;padding:22px;background:linear-gradient(135deg,#fffdf9,#f7f1e8);border:1px solid var(--border)}
    .setting-brand{display:flex;align-items:center;gap:14px}
    .setting-brand-logo{height:86px;min-width:86px;border-radius:24px;display:flex;align-items:center;justify-content:center;background:transparent;border:none;box-shadow:none;overflow:hidden}
    .setting-brand-logo.has-image{width:auto;max-width:240px;height:86px;padding:10px 16px;background:transparent;border:none;box-shadow:none}
    .setting-brand-logo img{height:100%;width:auto;object-fit:contain;padding:16px}
    .setting-brand-logo.has-image img{padding:0}
    .setting-brand-name{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#203746;line-height:1}
    .setting-brand-sub{font-size:12px;color:#70828c;margin-top:10px;letter-spacing:.18em;text-transform:uppercase}
    .setting-field{margin-top:16px}
    .setting-label{display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}
    .setting-logo-input{display:flex;align-items:center;gap:12px;padding:14px;border:1px dashed var(--border-light);background:var(--bg-soft);border-radius:16px;flex-wrap:wrap}
    .setting-logo-input input[type="file"]{display:none}
    .setting-logo-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--fg);font-size:12px;font-weight:700;cursor:pointer}
    .setting-logo-btn:hover{border-color:var(--accent);background:#fffaf4}
    .setting-logo-name{font-size:13px;color:var(--muted)}
    .setting-current-logo{margin-top:14px;height:120px;min-width:120px;border-radius:24px;border:none;background:transparent;display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:none}
    .setting-current-logo.has-image{width:auto;max-width:100%;height:110px;padding:12px 16px;background:transparent;border:none;box-shadow:none}
    .setting-current-logo img{height:100%;width:auto;object-fit:contain;padding:12px}
    .setting-current-logo.has-image img{padding:0}
    .setting-current-logo.empty{color:var(--muted);font-size:12px;text-align:center;padding:10px;background:var(--bg-soft)}
    .setting-logo-preview{display:none;position:relative;margin-top:14px;width:min(100%,240px)}
    .setting-logo-preview.show{display:block}
    .setting-logo-preview img{width:100%;height:110px;border-radius:24px;border:1px solid var(--border);background:#fff;object-fit:contain;padding:12px}
    .setting-logo-remove{position:absolute;top:8px;right:8px;width:28px;height:28px;border:none;border-radius:999px;background:rgba(0,0,0,.68);color:#fff;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center}
    .setting-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}
    .setting-help{font-size:12px;color:var(--muted);line-height:1.6;margin-top:8px}
    .setting-toggle{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 16px;border:1px solid var(--border);border-radius:16px;background:var(--bg-soft)}
    .setting-toggle-copy{display:grid;gap:4px}
    .setting-toggle-title{font-size:14px;font-weight:700;color:var(--fg)}
    .setting-toggle-sub{font-size:12px;color:var(--muted);line-height:1.5}
    .setting-switch{position:relative;width:56px;height:32px;display:inline-flex;flex-shrink:0}
    .setting-switch input{opacity:0;width:0;height:0}
    .setting-switch-slider{position:absolute;inset:0;border-radius:999px;background:#d7d0c4;transition:.2s ease;cursor:pointer}
    .setting-switch-slider::after{content:'';position:absolute;top:4px;left:4px;width:24px;height:24px;border-radius:999px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.15);transition:.2s ease}
    .setting-switch input:checked + .setting-switch-slider{background:var(--accent)}
    .setting-switch input:checked + .setting-switch-slider::after{transform:translateX(24px)}
    .setting-modal-mask{position:fixed;inset:0;background:rgba(8,15,12,.55);opacity:0;pointer-events:none;transition:opacity .25s ease;z-index:120}
    .setting-modal-mask.show{opacity:1;pointer-events:auto}
    .setting-crop-modal,.setting-camera-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%) scale(.96);width:min(720px,calc(100vw - 24px));background:var(--bg-raised);border:1px solid var(--border);border-radius:24px;box-shadow:var(--shadow-lg);z-index:130;opacity:0;pointer-events:none;transition:.25s ease;padding:20px}
    .setting-crop-modal.show,.setting-camera-modal.show{opacity:1;pointer-events:auto;transform:translate(-50%,-50%) scale(1)}
    .setting-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
    .setting-modal-title{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--fg)}
    .setting-modal-sub{font-size:13px;color:var(--muted);line-height:1.6;margin-top:4px}
    .setting-crop-layout{display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:18px}
    .setting-crop-stage{display:flex;align-items:center;justify-content:center;min-height:420px;background:var(--bg-soft);border:1px solid var(--border);border-radius:20px;padding:16px}
    .setting-crop-frame{position:relative;width:var(--setting-crop-frame-width,320px);height:var(--setting-crop-frame-height,180px);max-width:100%;border-radius:26px;overflow:hidden;background:#efe8de;box-shadow:0 0 0 1px rgba(0,0,0,.04);touch-action:none;cursor:grab}
    .setting-crop-frame.dragging{cursor:grabbing}
    .setting-crop-frame img{position:absolute;left:50%;top:50%;max-width:none;user-select:none;pointer-events:none}
    .setting-crop-side{display:flex;flex-direction:column;gap:14px}
    .setting-crop-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
    .setting-crop-preview{width:100%;max-width:180px;aspect-ratio:var(--setting-crop-preview-ratio,16 / 9);border-radius:20px;overflow:hidden;border:1px solid var(--border);background:#fff}
    .setting-crop-preview canvas{width:100%;height:100%;display:block}
    .setting-camera-preview{position:relative;overflow:hidden;border-radius:20px;background:#111;aspect-ratio:1/1;border:1px solid var(--border)}
    .setting-camera-preview video{width:100%;height:100%;object-fit:cover;display:block;transform:scaleX(-1)}
    .setting-modal-actions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:18px}
    @media (max-width:1040px){.setting-shell{grid-template-columns:1fr}}
    @media (max-width:820px){.setting-crop-layout{grid-template-columns:1fr}.setting-crop-side{order:-1}}
    @media (max-width:640px){.setting-brand{align-items:flex-start}.setting-brand-logo.has-image{width:min(100%,170px);height:74px}.setting-current-logo.has-image,.setting-logo-preview{width:min(100%,210px)}}
</style>

<div class="setting-page">
    <div class="member-toolbar">
        <div>
            <h1 class="setting-title">Setting</h1>
            <div class="setting-sub">Ubah identitas tampilan aplikasi dari satu halaman edit. Tidak ada menu tambah data lain di sini.</div>
        </div>
        <div class="member-badge"><i data-lucide="settings-2" class="w-3.5 h-3.5"></i> Akses superadmin</div>
    </div>

    <div class="setting-shell">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="setting-card setting-card-main js-setting-form">
            @csrf
            @method('PUT')

            <div class="setting-field">
                <label class="setting-label" for="app_name">Nama Aplikasi</label>
                <input id="app_name" name="app_name" class="form-input px-3 py-3 text-sm" value="{{ old('app_name', $appName) }}" required>
            </div>

            <div class="setting-field">
                <div class="setting-toggle">
                    <div class="setting-toggle-copy">
                        <div class="setting-toggle-title">Tampilkan Nama Aplikasi</div>
                        <div class="setting-toggle-sub">Matikan kalau yang tampil hanya logo aplikasi saja.</div>
                    </div>
                    <label class="setting-switch" for="show_app_name">
                        <input id="show_app_name" name="show_app_name" type="checkbox" value="1" {{ old('show_app_name', $showAppName ? '1' : null) ? 'checked' : '' }}>
                        <span class="setting-switch-slider"></span>
                    </label>
                </div>
            </div>

            <div class="setting-field">
                <label class="setting-label" for="discord_webhook_url">Discord Webhook URL</label>
                <input id="discord_webhook_url" name="discord_webhook_url" class="form-input px-3 py-3 text-sm" value="{{ old('discord_webhook_url', $settings['discord_webhook_url']->value ?? '') }}" placeholder="https://discord.com/api/webhooks/...">
                <div class="setting-help">URL Webhook Discord untuk notifikasi transaksi (edit/hapus data). Kosongkan untuk menonaktifkan.</div>
            </div>

            <div class="setting-field">
                <label class="setting-label">Logo Aplikasi</label>
                <div class="setting-logo-input" id="settingLogoUploadRoot">
                    <input id="app_logo" type="file" name="app_logo" accept="image/*" class="js-setting-file-input">
                    <input type="hidden" name="remove_logo" id="remove_logo" value="0">
                    <input type="file" accept="image/*" capture="environment" class="js-setting-camera-input">
                    <input type="file" accept="image/*" class="js-setting-gallery-input">
                    <button type="button" class="setting-logo-btn js-setting-open-camera"><i data-lucide="camera" class="w-4 h-4"></i> Foto Langsung</button>
                    <button type="button" class="setting-logo-btn js-setting-open-gallery"><i data-lucide="image" class="w-4 h-4"></i> Pilih dari Galeri</button>
                    <span id="settingLogoName" class="setting-logo-name">Tidak ada file baru dipilih</span>
                </div>
                <div class="setting-help">Biarkan kosong kalau tidak ingin mengganti logo.</div>
                <div id="settingLogoPreviewWrap" class="setting-logo-preview">
                    <img id="settingLogoPreviewImage" alt="Preview logo baru">
                    <button type="button" class="setting-logo-remove" id="settingLogoRemove">X</button>
                </div>

                @if ($appLogo)
                    <div class="setting-current-logo has-image">
                        <img src="{{ asset($appLogo) }}" alt="{{ $appName }}">
                    </div>
                @else
                    <div class="setting-current-logo empty">Belum ada logo aplikasi</div>
                @endif
            </div>

            <div class="setting-actions">
                <div class="setting-help js-setting-save-state">Ubah data lalu tekan Simpan Sekarang.</div>
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" type="submit">Simpan Sekarang</button>
            </div>
        </form>

        <div class="setting-card setting-preview">
            <div class="setting-preview-shell">
                <div class="setting-brand">
                    <div class="setting-brand-logo{{ $appLogo ? ' has-image' : '' }}" id="settingBrandLogo">
                        @if ($appLogo)
                            <img id="settingPreviewLogo" src="{{ asset($appLogo) }}" alt="{{ $appName }}">
                        @else
                            <i id="settingPreviewIcon" data-lucide="book-open" style="width:26px;height:26px;color:#7A5A28;"></i>
                            <img id="settingPreviewLogo" src="" alt="{{ $appName }}" style="display:none;">
                        @endif
                    </div>
                    <div id="settingPreviewNameWrap" style="{{ old('show_app_name', $showAppName ? '1' : null) ? '' : 'display:none;' }}">
                        <div id="settingPreviewName" class="setting-brand-name">{{ old('app_name', $appName) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="settingModalMask" class="setting-modal-mask"></div>
<div id="settingCameraModal" class="setting-camera-modal" aria-hidden="true">
    <div class="setting-modal-head">
        <div>
            <div class="setting-modal-title">Foto Langsung</div>
            <div class="setting-modal-sub">Ambil foto logo sekarang lalu crop dulu sebelum dipakai.</div>
        </div>
        <button type="button" class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" id="settingCameraClose">Tutup</button>
    </div>
    <div class="setting-camera-preview">
        <video id="settingCameraVideo" autoplay playsinline muted></video>
    </div>
    <div class="setting-help">Perlu izin kamera dari browser. Biasanya berjalan di HTTPS atau localhost.</div>
    <div class="setting-modal-actions">
        <button type="button" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" id="settingCameraCancel">Batal</button>
        <button type="button" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" id="settingCameraCapture">Ambil Foto</button>
    </div>
</div>

<div id="settingCropModal" class="setting-crop-modal" aria-hidden="true">
    <div class="setting-modal-head">
        <div>
            <div class="setting-modal-title">Atur Logo</div>
            <div class="setting-modal-sub">Geser dan zoom foto/logo sebelum dipakai di aplikasi.</div>
        </div>
        <button type="button" class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" id="settingCropClose">Tutup</button>
    </div>
    <div class="setting-crop-layout">
        <div class="setting-crop-stage">
            <div class="setting-crop-frame" id="settingCropFrame">
                <img id="settingCropImage" alt="Crop logo">
            </div>
        </div>
        <div class="setting-crop-side">
            <div class="setting-help">Drag foto/logo di area crop untuk menentukan bagian yang dipakai.</div>
            <div>
                <div class="setting-crop-label">Zoom</div>
                <input id="settingCropZoom" type="range" min="1" max="3" step="0.01" value="1" class="w-full">
                <div id="settingCropZoomValue" class="setting-help">100%</div>
            </div>
            <div>
                <div class="setting-crop-label">Preview</div>
                <div class="setting-crop-preview">
                    <canvas id="settingCropCanvas" width="320" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="setting-modal-actions">
        <button type="button" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" id="settingCropCancel">Batal</button>
        <button type="button" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" id="settingCropApply">Pakai Logo Ini</button>
    </div>
</div>

@include('admin.settings._autosave-script')
@endsection
