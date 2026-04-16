@extends('layouts.admin')

@section('content')
@php($title = 'Setting')
@php($eyebrow = 'Khusus Superadmin')
@php($appName = $settings['app_name']->value ?? 'LibraVault')
@php($appLogo = \App\Models\Setting::appLogoPath())
@php($appColor = $settings['app_color']->value ?? '#c4956a')

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
    .setting-brand-logo{height:86px;min-width:86px;border-radius:24px;display:flex;align-items:center;justify-content:center;background:var(--preview-color);border:1px solid rgba(255,255,255,.92);box-shadow:0 14px 30px rgba(0,0,0,.08);overflow:hidden}
    .setting-brand-logo.has-image{width:auto;max-width:240px;height:86px;padding:10px 16px;background:transparent;border:none;box-shadow:none}
    .setting-brand-logo img{height:100%;width:auto;object-fit:contain;padding:16px}
    .setting-brand-logo.has-image img{padding:0}
    .setting-brand-name{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#203746;line-height:1}
    .setting-brand-sub{font-size:12px;color:#70828c;margin-top:10px;letter-spacing:.18em;text-transform:uppercase}
    .setting-color-chip{display:inline-flex;align-items:center;gap:10px;margin-top:18px;padding:14px 20px;border-radius:18px;background:#fff;border:1px solid var(--border);font-size:13px;color:#394b53;font-weight:700;box-shadow:0 10px 24px rgba(196,149,106,.10)}
    .setting-color-dot{width:18px;height:18px;border-radius:999px;background:var(--preview-color)}
    .setting-field{margin-top:16px}
    .setting-label{display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}
    .setting-logo-input{display:flex;align-items:center;gap:12px;padding:14px;border:1px dashed var(--border-light);background:var(--bg-soft);border-radius:16px;flex-wrap:wrap}
    .setting-logo-input input[type="file"]{display:none}
    .setting-logo-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--fg);font-size:12px;font-weight:700;cursor:pointer}
    .setting-logo-btn:hover{border-color:var(--accent);background:#fffaf4}
    .setting-logo-name{font-size:13px;color:var(--muted)}
    .setting-current-logo{margin-top:14px;height:120px;min-width:120px;border-radius:24px;border:1px solid rgba(255,255,255,.92);background:var(--preview-color);display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 14px 28px rgba(0,0,0,.08)}
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

    <div class="setting-shell" style="--preview-color: {{ old('app_color', $appColor) }}">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="setting-card setting-card-main" data-async="true" data-refresh-targets=".setting-card-main, .setting-preview, .topbar-brand-mark, .sidebar-brand-mark">
            @csrf
            @method('PUT')

            <div class="setting-field">
                <label class="setting-label" for="app_name">Nama Aplikasi</label>
                <input id="app_name" name="app_name" class="form-input px-3 py-3 text-sm" value="{{ old('app_name', $appName) }}" required>
            </div>

            <div class="setting-field">
                <label class="setting-label" for="app_color">Warna Background Logo</label>
                <input id="app_color" name="app_color" type="color" class="form-input px-3 py-3 text-sm" value="{{ old('app_color', $appColor) }}" style="height:54px;padding:8px;">
                <div class="setting-help">Warna ini hanya dipakai untuk background area logo pada preview identitas aplikasi.</div>
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
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" type="submit">Simpan Perubahan</button>
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
                    <div>
                        <div id="settingPreviewName" class="setting-brand-name">{{ old('app_name', $appName) }}</div>
                    </div>
                </div>
                <div class="setting-color-chip">
                    <span class="setting-color-dot" id="settingColorDot"></span>
                    <span id="settingColorText">{{ strtoupper(old('app_color', $appColor)) }}</span>
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

<script>
    const appNameInput = document.getElementById('app_name');
    const appColorInput = document.getElementById('app_color');
    const appLogoInput = document.getElementById('app_logo');
    const settingPreviewName = document.getElementById('settingPreviewName');
    const settingShell = document.querySelector('.setting-shell');
    const settingColorText = document.getElementById('settingColorText');
    const settingLogoName = document.getElementById('settingLogoName');
    const settingPreviewLogo = document.getElementById('settingPreviewLogo');
    const settingPreviewIcon = document.getElementById('settingPreviewIcon');
    const settingLogoPreviewWrap = document.getElementById('settingLogoPreviewWrap');
    const settingLogoPreviewImage = document.getElementById('settingLogoPreviewImage');
    const settingLogoRemove = document.getElementById('settingLogoRemove');
    const settingUploadRoot = document.getElementById('settingLogoUploadRoot');
    const settingCameraInput = settingUploadRoot.querySelector('.js-setting-camera-input');
    const settingGalleryInput = settingUploadRoot.querySelector('.js-setting-gallery-input');
    const settingModalMask = document.getElementById('settingModalMask');
    const settingCameraModal = document.getElementById('settingCameraModal');
    const settingCameraVideo = document.getElementById('settingCameraVideo');
    const settingCropModal = document.getElementById('settingCropModal');
    const settingCropFrame = document.getElementById('settingCropFrame');
    const settingCropImage = document.getElementById('settingCropImage');
    const settingCropCanvas = document.getElementById('settingCropCanvas');
    const settingCropZoom = document.getElementById('settingCropZoom');
    const settingCropZoomValue = document.getElementById('settingCropZoomValue');
    const settingBrandLogo = document.getElementById('settingBrandLogo');
    let settingCropSourceUrl = '';
    let settingCropSourceImage = null;
    let settingCropScale = 1;
    let settingCropOffsetX = 0;
    let settingCropOffsetY = 0;
    let settingCropDragging = false;
    let settingDragStartX = 0;
    let settingDragStartY = 0;
    let settingDragOriginX = 0;
    let settingDragOriginY = 0;
    let settingActiveSourceInput = null;
    let settingCameraStream = null;
    let settingCropFrameWidth = 320;
    let settingCropFrameHeight = 180;

    appNameInput.addEventListener('input', function () {
        settingPreviewName.textContent = appNameInput.value.trim() || 'LibraVault';
    });

    appColorInput.addEventListener('input', function () {
        settingShell.style.setProperty('--preview-color', appColorInput.value);
        settingColorText.textContent = appColorInput.value.toUpperCase();
    });

    function syncSettingPreview(previewUrl, fileName) {
        document.getElementById('remove_logo').value = '0';
        settingLogoName.textContent = fileName;
        settingLogoPreviewImage.src = previewUrl;
        settingLogoPreviewWrap.classList.add('show');
        settingPreviewLogo.src = previewUrl;
        settingPreviewLogo.style.display = 'block';
        settingBrandLogo.classList.add('has-image');
        if (settingPreviewIcon) {
            settingPreviewIcon.style.display = 'none';
        }
    }

    function resetSettingUpload() {
        appLogoInput.value = '';
        settingCameraInput.value = '';
        settingGalleryInput.value = '';
        document.getElementById('remove_logo').value = '1';
        settingLogoName.textContent = 'Tidak ada file baru dipilih';
        settingLogoPreviewWrap.classList.remove('show');
        settingLogoPreviewImage.removeAttribute('src');
        settingPreviewLogo.style.display = 'none';
        settingPreviewLogo.removeAttribute('src');
        settingBrandLogo.classList.remove('has-image');
        if (settingPreviewIcon) {
            settingPreviewIcon.style.display = 'block';
        }
    }

    function openSettingCropModal(sourceInput, file) {
        settingActiveSourceInput = sourceInput;

        if (settingCropSourceUrl) {
            URL.revokeObjectURL(settingCropSourceUrl);
        }

        settingCropSourceUrl = URL.createObjectURL(file);
        settingCropSourceImage = new Image();
        settingCropSourceImage.onload = function () {
            updateSettingCropFrameSize(settingCropSourceImage.width, settingCropSourceImage.height);
            settingCropScale = 1;
            settingCropOffsetX = 0;
            settingCropOffsetY = 0;
            settingCropZoom.value = '1';
            settingCropZoomValue.textContent = '100%';
            settingCropImage.src = settingCropSourceUrl;
            renderSettingCrop();
            settingModalMask.classList.add('show');
            settingCropModal.classList.add('show');
            settingCropModal.setAttribute('aria-hidden', 'false');
        };
        settingCropSourceImage.src = settingCropSourceUrl;
    }

    function updateSettingCropFrameSize(imageWidth, imageHeight) {
        const maxWidth = window.innerWidth < 640 ? 260 : 420;
        const minWidth = window.innerWidth < 640 ? 180 : 220;
        const maxHeight = window.innerWidth < 640 ? 180 : 240;
        const minHeight = 120;
        const safeRatio = imageWidth > 0 && imageHeight > 0 ? (imageWidth / imageHeight) : (16 / 9);

        settingCropFrameWidth = Math.max(minWidth, Math.min(maxWidth, Math.round(maxHeight * safeRatio)));
        settingCropFrameHeight = Math.max(minHeight, Math.min(maxHeight, Math.round(settingCropFrameWidth / safeRatio)));

        if (settingCropFrameHeight > maxHeight) {
            settingCropFrameHeight = maxHeight;
            settingCropFrameWidth = Math.max(minWidth, Math.min(maxWidth, Math.round(settingCropFrameHeight * safeRatio)));
        }

        settingCropFrame.style.setProperty('--setting-crop-frame-width', settingCropFrameWidth + 'px');
        settingCropFrame.style.setProperty('--setting-crop-frame-height', settingCropFrameHeight + 'px');
        document.documentElement.style.setProperty('--setting-crop-preview-ratio', settingCropFrameWidth + ' / ' + settingCropFrameHeight);
        settingCropCanvas.width = settingCropFrameWidth;
        settingCropCanvas.height = settingCropFrameHeight;
    }

    function closeSettingCropModal(resetInput = true) {
        settingCropModal.classList.remove('show');
        settingCropModal.setAttribute('aria-hidden', 'true');
        if (!settingCameraModal.classList.contains('show')) {
            settingModalMask.classList.remove('show');
        }
        if (resetInput && settingActiveSourceInput) {
            settingActiveSourceInput.value = '';
        }
    }

    async function openSettingCameraModal() {
        try {
            settingCameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' }
                },
                audio: false
            });

            settingCameraVideo.srcObject = settingCameraStream;
            settingModalMask.classList.add('show');
            settingCameraModal.classList.add('show');
            settingCameraModal.setAttribute('aria-hidden', 'false');
        } catch (error) {
            alert('Kamera tidak bisa dibuka. Pastikan izin kamera aktif.');
        }
    }

    function stopSettingCameraStream() {
        if (!settingCameraStream) {
            return;
        }

        settingCameraStream.getTracks().forEach(function (track) {
            track.stop();
        });
        settingCameraStream = null;
        settingCameraVideo.srcObject = null;
    }

    function closeSettingCameraModal() {
        settingCameraModal.classList.remove('show');
        settingCameraModal.setAttribute('aria-hidden', 'true');
        stopSettingCameraStream();
        if (!settingCropModal.classList.contains('show')) {
            settingModalMask.classList.remove('show');
        }
    }

    function renderSettingCrop() {
        if (!settingCropSourceImage) {
            return;
        }

        const frameWidth = settingCropFrame.clientWidth || settingCropFrameWidth;
        const frameHeight = settingCropFrame.clientHeight || settingCropFrameHeight;
        const baseScale = Math.max(frameWidth / settingCropSourceImage.width, frameHeight / settingCropSourceImage.height);
        const scale = baseScale * settingCropScale;
        const drawWidth = settingCropSourceImage.width * scale;
        const drawHeight = settingCropSourceImage.height * scale;
        const maxOffsetX = Math.max((drawWidth - frameWidth) / 2, 0);
        const maxOffsetY = Math.max((drawHeight - frameHeight) / 2, 0);
        settingCropOffsetX = Math.min(Math.max(settingCropOffsetX, -maxOffsetX), maxOffsetX);
        settingCropOffsetY = Math.min(Math.max(settingCropOffsetY, -maxOffsetY), maxOffsetY);
        const left = ((frameWidth - drawWidth) / 2) + settingCropOffsetX;
        const top = ((frameHeight - drawHeight) / 2) + settingCropOffsetY;

        settingCropImage.style.width = drawWidth + 'px';
        settingCropImage.style.height = drawHeight + 'px';
        settingCropImage.style.transform = 'translate(calc(-50% + ' + settingCropOffsetX + 'px), calc(-50% + ' + settingCropOffsetY + 'px))';

        const ctx = settingCropCanvas.getContext('2d');
        ctx.clearRect(0, 0, settingCropCanvas.width, settingCropCanvas.height);
        ctx.drawImage(settingCropSourceImage, left, top, drawWidth, drawHeight);
    }

    function applySettingCrop() {
        if (!settingCropSourceImage) {
            return;
        }

        settingCropCanvas.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            const originalName = settingActiveSourceInput && settingActiveSourceInput.files && settingActiveSourceInput.files[0]
                ? settingActiveSourceInput.files[0].name
                : 'app-logo.png';
            const croppedFile = new File([blob], originalName, { type: 'image/png' });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            appLogoInput.files = transfer.files;

            const previewUrl = URL.createObjectURL(blob);
            syncSettingPreview(previewUrl, croppedFile.name);
            if (settingActiveSourceInput) {
                settingActiveSourceInput.value = '';
            }
            closeSettingCropModal(false);
        }, 'image/png');
    }

    function captureSettingCameraPhoto() {
        if (!settingCameraVideo.videoWidth || !settingCameraVideo.videoHeight) {
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = settingCameraVideo.videoWidth;
        canvas.height = settingCameraVideo.videoHeight;
        const context = canvas.getContext('2d');
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(settingCameraVideo, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            const file = new File([blob], 'camera-logo.png', { type: 'image/png' });
            closeSettingCameraModal();
            openSettingCropModal(null, file);
        }, 'image/png');
    }

    settingUploadRoot.querySelector('.js-setting-open-camera').addEventListener('click', function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            settingCameraInput.click();
            return;
        }

        openSettingCameraModal();
    });

    settingUploadRoot.querySelector('.js-setting-open-gallery').addEventListener('click', function () {
        settingGalleryInput.click();
    });

    [settingCameraInput, settingGalleryInput].forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.files && input.files.length) {
                openSettingCropModal(input, input.files[0]);
            }
        });
    });

    settingLogoRemove.addEventListener('click', resetSettingUpload);
    settingModalMask.addEventListener('click', function () {
        closeSettingCameraModal();
        closeSettingCropModal();
    });
    settingCropZoom.addEventListener('input', function () {
        settingCropScale = Number(settingCropZoom.value);
        settingCropZoomValue.textContent = Math.round(settingCropScale * 100) + '%';
        renderSettingCrop();
    });

    settingCropFrame.addEventListener('pointerdown', function (event) {
        if (!settingCropSourceImage) {
            return;
        }

        settingCropDragging = true;
        settingDragStartX = event.clientX;
        settingDragStartY = event.clientY;
        settingDragOriginX = settingCropOffsetX;
        settingDragOriginY = settingCropOffsetY;
        settingCropFrame.classList.add('dragging');
        settingCropFrame.setPointerCapture(event.pointerId);
    });

    settingCropFrame.addEventListener('pointermove', function (event) {
        if (!settingCropDragging) {
            return;
        }

        settingCropOffsetX = settingDragOriginX + (event.clientX - settingDragStartX);
        settingCropOffsetY = settingDragOriginY + (event.clientY - settingDragStartY);
        renderSettingCrop();
    });

    function stopSettingDrag(event) {
        if (!settingCropDragging) {
            return;
        }

        settingCropDragging = false;
        settingCropFrame.classList.remove('dragging');
        if (event) {
            settingCropFrame.releasePointerCapture(event.pointerId);
        }
    }

    settingCropFrame.addEventListener('pointerup', stopSettingDrag);
    settingCropFrame.addEventListener('pointercancel', stopSettingDrag);
    window.addEventListener('resize', function () {
        if (!settingCropSourceImage) {
            return;
        }

        updateSettingCropFrameSize(settingCropSourceImage.width, settingCropSourceImage.height);
        renderSettingCrop();
    });
    document.getElementById('settingCameraClose').addEventListener('click', closeSettingCameraModal);
    document.getElementById('settingCameraCancel').addEventListener('click', closeSettingCameraModal);
    document.getElementById('settingCameraCapture').addEventListener('click', captureSettingCameraPhoto);
    document.getElementById('settingCropClose').addEventListener('click', function () { closeSettingCropModal(); });
    document.getElementById('settingCropCancel').addEventListener('click', function () { closeSettingCropModal(); });
    document.getElementById('settingCropApply').addEventListener('click', applySettingCrop);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSettingCameraModal();
            closeSettingCropModal();
        }
    });

    if (window.lucide) {
        window.lucide.createIcons();
    }
</script>
@endsection
