@extends('layouts.admin')

@section('content')
@php($title = 'Profil Saya')

<style>
    .profile-shell{display:grid;grid-template-columns:minmax(320px,380px) minmax(0,1fr);gap:20px}
    .profile-card,.profile-form{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow-sm)}
    .profile-card{padding:24px}
    .profile-form{padding:24px}
    .profile-title{font-family:'Playfair Display',serif;font-size:30px;font-weight:700;color:var(--fg);letter-spacing:-.03em}
    .profile-sub{font-size:14px;color:var(--muted);line-height:1.7;margin-top:8px}
    .profile-photo{width:120px;height:120px;border-radius:28px;overflow:hidden;background:linear-gradient(135deg,var(--accent),var(--accent-light));display:flex;align-items:center;justify-content:center;color:#fff;font-size:34px;font-weight:700;box-shadow:var(--shadow-md)}
    .profile-photo img{width:100%;height:100%;object-fit:cover}
    .profile-photo.is-editable{cursor:pointer;position:relative}
    .profile-photo.is-editable::after{content:'Atur';position:absolute;inset:auto 10px 10px auto;padding:4px 8px;border-radius:999px;background:rgba(8,15,12,.72);color:#fff;font-size:10px;font-weight:700;letter-spacing:.04em}
    .profile-meta{display:grid;gap:12px;margin-top:22px}
    .profile-box{padding:14px 16px;border-radius:16px;background:var(--bg-soft);border:1px solid var(--border)}
    .profile-box-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--dim)}
    .profile-box-value{font-size:14px;color:var(--fg);margin-top:6px;line-height:1.5}
    .profile-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .profile-upload{position:relative;padding:18px;border-radius:18px;border:1px dashed rgba(196,149,106,.45);background:linear-gradient(135deg,#fffdf9,#f7efe6)}
    .profile-upload input[type="file"]{display:none}
    .profile-upload-head{display:flex;align-items:center;gap:14px}
    .profile-upload-icon{width:54px;height:54px;border-radius:16px;background:linear-gradient(135deg,var(--accent),var(--accent-light));display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:var(--shadow-sm);flex-shrink:0}
    .profile-upload-title{font-size:15px;font-weight:700;color:var(--fg)}
    .profile-upload-sub{font-size:12px;color:var(--muted);line-height:1.6;margin-top:4px}
    .profile-upload-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}
    .profile-upload-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border-radius:14px;border:1px solid var(--border);background:#fff;color:var(--fg);font-size:13px;font-weight:700;cursor:pointer;transition:.2s}
    .profile-upload-btn:hover{border-color:var(--accent);background:#fffaf4}
    .profile-upload-name{font-size:12px;color:var(--muted);line-height:1.5;margin-top:12px}
    .profile-upload-name.is-empty{display:none}
    .profile-upload-status{display:none;margin-top:12px;padding:10px 12px;border-radius:12px;background:#eef8f1;border:1px solid rgba(39,108,75,.18);font-size:12px;color:#1f573d}
    .profile-upload-status.show{display:block}
    .profile-upload-preview{display:none;position:relative;margin-top:14px;width:132px}
    .profile-upload-preview.show{display:block}
    .profile-upload-preview img{width:132px;height:132px;border-radius:20px;object-fit:cover;border:1px solid var(--border);background:#fff}
    .profile-upload-preview.is-editable img{cursor:pointer}
    .profile-upload-remove{position:absolute;top:8px;right:8px;width:28px;height:28px;border:none;border-radius:999px;background:rgba(0,0,0,.68);color:#fff;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center}
    .profile-hint{font-size:12px;color:var(--muted);line-height:1.6;margin-top:10px}
    .profile-modal-mask{position:fixed;inset:0;background:rgba(8,15,12,.55);opacity:0;pointer-events:none;transition:opacity .25s ease;z-index:120}
    .profile-modal-mask.show{opacity:1;pointer-events:auto}
    .profile-crop-modal,.profile-camera-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%) scale(.96);width:min(720px,calc(100vw - 24px));max-height:calc(100vh - 24px);overflow-y:auto;background:var(--bg-raised);border:1px solid var(--border);border-radius:24px;box-shadow:var(--shadow-lg);z-index:130;opacity:0;pointer-events:none;transition:.25s ease;padding:20px}
    .profile-crop-modal.show,.profile-camera-modal.show{opacity:1;pointer-events:auto;transform:translate(-50%,-50%) scale(1)}
    .profile-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
    .profile-modal-title{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--fg)}
    .profile-modal-sub{font-size:13px;color:var(--muted);line-height:1.6;margin-top:4px}
    .profile-crop-layout{display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:18px}
    .profile-crop-stage{display:flex;align-items:center;justify-content:center;min-height:360px;background:var(--bg-soft);border:1px solid var(--border);border-radius:20px;padding:16px}
    .profile-crop-frame{position:relative;width:min(280px,70vw);height:min(280px,70vw);border-radius:26px;overflow:hidden;background:#efe8de;box-shadow:0 0 0 1px rgba(0,0,0,.04);touch-action:none;cursor:grab}
    .profile-crop-frame.dragging{cursor:grabbing}
    .profile-crop-frame img{position:absolute;left:50%;top:50%;max-width:none;user-select:none;pointer-events:none}
    .profile-crop-side{display:flex;flex-direction:column;gap:14px}
    .profile-crop-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
    .profile-crop-preview{width:140px;height:140px;border-radius:20px;overflow:hidden;border:1px solid var(--border);background:#fff}
    .profile-crop-preview canvas{width:100%;height:100%;display:block}
    .profile-camera-preview{position:relative;overflow:hidden;border-radius:20px;background:#111;aspect-ratio:1/1;border:1px solid var(--border)}
    .profile-camera-preview video{width:100%;height:100%;object-fit:cover;display:block;transform:scaleX(-1)}
    .profile-modal-actions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:18px}
    @media (max-width:1000px){.profile-shell{grid-template-columns:1fr}}
    @media (max-width:820px){.profile-crop-layout{grid-template-columns:1fr}.profile-crop-side{order:-1}}
    @media (max-width:700px){.profile-form-grid,.profile-upload-actions{grid-template-columns:1fr}.profile-crop-stage{min-height:300px}.profile-crop-frame{width:min(220px,68vw);height:min(220px,68vw)}}
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Profil Saya</h1>
            <p class="member-subtitle">Semua akun bisa ganti nama, foto profil, dan data akun dari halaman ini.</p>
        </div>
        <div class="member-badge"><i data-lucide="user-round-cog" class="w-3.5 h-3.5"></i> Akun pribadi</div>
    </div>

    <div class="profile-shell">
        <aside class="profile-card">
            <div class="profile-title">Akun Aktif</div>
            <div class="profile-sub">Foto dan nama di panel ini akan ikut berubah setelah profil disimpan.</div>

                <div style="margin-top:20px;display:flex;justify-content:center;">
                <div class="profile-photo is-editable" id="profileActivePhoto" title="Klik untuk atur ukuran foto">
                    @if ($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" id="profileActivePhotoImage">
                    @else
                        <span id="profileActivePhotoFallback">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
            </div>

            <div class="profile-meta">
                <div class="profile-box">
                    <div class="profile-box-label">Nama</div>
                    <div class="profile-box-value">{{ $user->name }}</div>
                </div>
                <div class="profile-box">
                    <div class="profile-box-label">Role</div>
                    <div class="profile-box-value">{{ $user->role?->label ?? 'Tanpa role' }}</div>
                </div>
                <div class="profile-box">
                    <div class="profile-box-label">Username</div>
                    <div class="profile-box-value">{{ $user->username }}</div>
                </div>
                <div class="profile-box">
                    <div class="profile-box-label">Kelas / Jurusan</div>
                    <div class="profile-box-value">{{ $user->academicLabel() ?: 'Belum diisi' }}</div>
                </div>
            </div>
        </aside>

        <section class="profile-form">
            <div class="profile-title">Edit Profil</div>
            <div class="profile-sub">Ubah nama, email, foto, dan data akun lain. Password tidak ikut berubah dari form ini.</div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-3 mt-6" data-async="true" data-loading-label="Menyimpan foto..." data-success-call="handleProfileUpdateSuccess">
                @csrf
                @method('PUT')

                <div class="profile-form-grid">
                    <input name="name" value="{{ old('name', $user->name) }}" class="form-input px-3 py-3 text-sm" placeholder="Nama lengkap" required>
                    <input name="username" value="{{ old('username', $user->username) }}" class="form-input px-3 py-3 text-sm" placeholder="Username" required>
                </div>

                <div class="profile-form-grid">
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input px-3 py-3 text-sm" placeholder="Email" required>
                </div>

                <div class="profile-form-grid">
                    <input name="phone" value="{{ old('phone', $user->phone) }}" class="form-input px-3 py-3 text-sm" placeholder="No HP">
                    <input name="kelas" value="{{ old('kelas', $user->kelas) }}" class="form-input px-3 py-3 text-sm" placeholder="Kelas">
                </div>

                <div class="profile-form-grid">
                    <input name="jurusan" value="{{ old('jurusan', $user->jurusan) }}" class="form-input px-3 py-3 text-sm" placeholder="Jurusan">
                </div>

                <div class="profile-upload" id="profileUploadRoot">
                    <input type="file" name="profile_photo" accept="image/*" class="js-profile-file-input">
                    <input type="file" accept="image/*" capture="environment" class="js-profile-camera-input">
                    <input type="file" accept="image/*" class="js-profile-gallery-input">
                    <div class="profile-upload-head">
                        <div class="profile-upload-icon">
                            <i data-lucide="image-plus" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="profile-upload-title">Foto Profil</div>
                            <div class="profile-upload-sub">Upload foto baru, crop dulu seperti IG, atau ambil foto langsung dari kamera.</div>
                        </div>
                    </div>
                    <div class="profile-upload-actions">
                        <button type="button" class="profile-upload-btn js-profile-open-camera"><i data-lucide="camera" class="w-4 h-4"></i> Foto Langsung</button>
                        <button type="button" class="profile-upload-btn js-profile-open-gallery"><i data-lucide="image" class="w-4 h-4"></i> Pilih dari Galeri</button>
                    </div>
                    <div class="profile-upload-name is-empty" id="profilePhotoName">Belum ada file dipilih.</div>
                    <div class="profile-upload-status" id="profilePhotoStatus">Foto sedang dioptimalkan supaya upload lebih cepat.</div>
                    <div class="profile-upload-preview is-editable" id="profilePhotoPreview" title="Klik foto untuk atur ukuran lagi">
                        <img id="profilePhotoPreviewImage" alt="Preview foto profil">
                        <button type="button" class="profile-upload-remove" id="profilePhotoRemove">X</button>
                    </div>
                    <div class="profile-hint">Tips: pakai foto wajah atau logo pribadi agar mudah dikenali di semua halaman akun.</div>
                </div>

                <button type="submit" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold">Simpan Perubahan</button>
            </form>
        </section>
    </div>
</div>

<div id="profileModalMask" class="profile-modal-mask"></div>
<div id="profileCameraModal" class="profile-camera-modal" aria-hidden="true">
    <div class="profile-modal-head">
        <div>
            <div class="profile-modal-title">Foto Langsung</div>
            <div class="profile-modal-sub">Ambil foto sekarang lalu atur crop sebelum dipakai jadi foto profil.</div>
        </div>
        <button type="button" class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" id="profileCameraClose">Tutup</button>
    </div>
    <div class="profile-camera-preview">
        <video id="profileCameraVideo" autoplay playsinline muted></video>
    </div>
    <div class="profile-hint">Perlu izin kamera dari browser. Biasanya berjalan di HTTPS atau localhost.</div>
    <div class="profile-modal-actions">
        <button type="button" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" id="profileCameraCancel">Batal</button>
        <button type="button" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" id="profileCameraCapture">Ambil Foto</button>
    </div>
</div>

<div id="profileCropModal" class="profile-crop-modal" aria-hidden="true">
    <div class="profile-modal-head">
        <div>
            <div class="profile-modal-title">Atur Foto Profil</div>
            <div class="profile-modal-sub">Geser dan zoom foto seperti aplikasi sosial media sebelum disimpan.</div>
        </div>
        <button type="button" class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" id="profileCropClose">Tutup</button>
    </div>
    <div class="profile-crop-layout">
        <div class="profile-crop-stage">
            <div class="profile-crop-frame" id="profileCropFrame">
                <img id="profileCropImage" alt="Crop foto profil">
            </div>
        </div>
        <div class="profile-crop-side">
            <div class="profile-hint">Drag foto langsung di area crop untuk menentukan bagian yang dipakai.</div>
            <div>
                <div class="profile-crop-label">Zoom</div>
                <input id="profileCropZoom" type="range" min="1" max="3" step="0.01" value="1" class="w-full">
                <div id="profileCropZoomValue" class="profile-hint">100%</div>
            </div>
            <div>
                <div class="profile-crop-label">Preview</div>
                <div class="profile-crop-preview">
                    <canvas id="profileCropCanvas" width="320" height="320"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="profile-modal-actions">
        <button type="button" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" id="profileCropCancel">Batal</button>
        <button type="button" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" id="profileCropApply">Pakai Foto Ini</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileUploadRoot = document.getElementById('profileUploadRoot');
        const profilePhotoName = document.getElementById('profilePhotoName');
        const profilePreview = document.getElementById('profilePhotoPreview');
        const profilePreviewImage = document.getElementById('profilePhotoPreviewImage');
        const profilePhotoStatus = document.getElementById('profilePhotoStatus');
        const profileRemove = document.getElementById('profilePhotoRemove');
        const activePhoto = document.getElementById('profileActivePhoto');
        const activePhotoImage = document.getElementById('profileActivePhotoImage');
        const activePhotoFallback = document.getElementById('profileActivePhotoFallback');
        const realInput = profileUploadRoot.querySelector('.js-profile-file-input');
        const cameraInput = profileUploadRoot.querySelector('.js-profile-camera-input');
        const galleryInput = profileUploadRoot.querySelector('.js-profile-gallery-input');
        const openCameraButton = profileUploadRoot.querySelector('.js-profile-open-camera');
        const openGalleryButton = profileUploadRoot.querySelector('.js-profile-open-gallery');
        const modalMask = document.getElementById('profileModalMask');
        const cameraModal = document.getElementById('profileCameraModal');
        const cameraVideo = document.getElementById('profileCameraVideo');
        const cropModal = document.getElementById('profileCropModal');
        const cropFrame = document.getElementById('profileCropFrame');
        const cropImage = document.getElementById('profileCropImage');
        const cropCanvas = document.getElementById('profileCropCanvas');
        const cropZoom = document.getElementById('profileCropZoom');
        const cropZoomValue = document.getElementById('profileCropZoomValue');
        let cropSourceUrl = '';
        let cropSourceImage = null;
        let cropScale = 1;
        let cropOffsetX = 0;
        let cropOffsetY = 0;
        let cropDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let dragOriginX = 0;
        let dragOriginY = 0;
        let activeSourceInput = null;
        let cameraStream = null;
        let currentPreviewUrl = '';
        let latestOriginalFile = null;

        function setUploadStatus(message = '') {
            if (!message) {
                profilePhotoStatus.textContent = '';
                profilePhotoStatus.classList.remove('show');
                return;
            }

            profilePhotoStatus.textContent = message;
            profilePhotoStatus.classList.add('show');
        }

        function syncActivePhoto(previewUrl) {
            if (!previewUrl) {
                return;
            }

            if (activePhotoFallback) {
                activePhotoFallback.remove();
            }

            let image = document.getElementById('profileActivePhotoImage');

            if (!image) {
                image = document.createElement('img');
                image.id = 'profileActivePhotoImage';
                image.alt = '{{ $user->name }}';
                activePhoto.appendChild(image);
            }

            image.src = previewUrl;
        }

        function syncGlobalProfilePhoto(previewUrl) {
            if (!previewUrl) {
                return;
            }

            document.querySelectorAll('.js-global-profile-fallback').forEach(function (fallback) {
                fallback.remove();
            });

            document.querySelectorAll('.js-global-profile-avatar').forEach(function (avatar) {
                let image = avatar.querySelector('.js-global-profile-photo');

                if (!image) {
                    image = document.createElement('img');
                    image.className = 'js-global-profile-photo';
                    image.alt = '{{ $user->name }}';
                    image.style.width = '100%';
                    image.style.height = '100%';
                    image.style.objectFit = 'cover';
                    avatar.appendChild(image);
                }

                image.src = previewUrl;
            });
        }

        function resetPreview() {
            realInput.value = '';
            cameraInput.value = '';
            galleryInput.value = '';
            profilePreview.classList.remove('show');
            profilePreviewImage.removeAttribute('src');
            profilePhotoName.textContent = 'Belum ada file dipilih.';
            profilePhotoName.classList.add('is-empty');
            setUploadStatus('');
            latestOriginalFile = null;
        }

        function openCropModal(sourceInput, file) {
            activeSourceInput = sourceInput;
            latestOriginalFile = file;

            if (cropSourceUrl) {
                URL.revokeObjectURL(cropSourceUrl);
            }

            cropSourceUrl = URL.createObjectURL(file);
            cropSourceImage = new Image();
            cropSourceImage.onload = function () {
                cropScale = 1;
                cropOffsetX = 0;
                cropOffsetY = 0;
                cropZoom.value = '1';
                cropZoomValue.textContent = '100%';
                cropImage.src = cropSourceUrl;
                renderCrop();
                modalMask.classList.add('show');
                cropModal.classList.add('show');
                cropModal.setAttribute('aria-hidden', 'false');
            };
            cropSourceImage.src = cropSourceUrl;
        }

        async function reopenCropFromPreview() {
            if (latestOriginalFile) {
                openCropModal(realInput, latestOriginalFile);
                return;
            }

            const previewSource = profilePreviewImage.getAttribute('src') || document.getElementById('profileActivePhotoImage')?.getAttribute('src');

            if (!previewSource) {
                return;
            }

            try {
                setUploadStatus('Foto lama yang tersimpan dibuka dari versi yang ada sekarang.');
                const response = await fetch(previewSource, { cache: 'no-store' });
                const blob = await response.blob();
                const file = new File([blob], 'profile-photo-edit.jpg', { type: blob.type || 'image/jpeg' });
                openCropModal(null, file);
            } catch (error) {
                setUploadStatus('Foto tidak bisa dibuka ulang. Pilih foto lagi dari galeri.');
            }
        }

        function closeCropModal(resetInput = true) {
            cropModal.classList.remove('show');
            cropModal.setAttribute('aria-hidden', 'true');
            if (!cameraModal.classList.contains('show')) {
                modalMask.classList.remove('show');
            }
            if (resetInput && activeSourceInput) {
                activeSourceInput.value = '';
            }
        }

        async function openCameraModal() {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'user' }
                    },
                    audio: false
                });

                cameraVideo.srcObject = cameraStream;
                modalMask.classList.add('show');
                cameraModal.classList.add('show');
                cameraModal.setAttribute('aria-hidden', 'false');
            } catch (error) {
                alert('Kamera tidak bisa dibuka. Pastikan izin kamera aktif.');
            }
        }

        function stopCameraStream() {
            if (!cameraStream) {
                return;
            }

            cameraStream.getTracks().forEach(function (track) {
                track.stop();
            });
            cameraStream = null;
            cameraVideo.srcObject = null;
        }

        function closeCameraModal() {
            cameraModal.classList.remove('show');
            cameraModal.setAttribute('aria-hidden', 'true');
            stopCameraStream();
            if (!cropModal.classList.contains('show')) {
                modalMask.classList.remove('show');
            }
        }

        function renderCrop() {
            if (!cropSourceImage) {
                return;
            }

            const frameWidth = cropFrame.clientWidth;
            const frameHeight = cropFrame.clientHeight;
            const baseScale = Math.max(frameWidth / cropSourceImage.width, frameHeight / cropSourceImage.height);
            const scale = baseScale * cropScale;
            const drawWidth = cropSourceImage.width * scale;
            const drawHeight = cropSourceImage.height * scale;
            const maxOffsetX = Math.max((drawWidth - frameWidth) / 2, 0);
            const maxOffsetY = Math.max((drawHeight - frameHeight) / 2, 0);
            cropOffsetX = Math.min(Math.max(cropOffsetX, -maxOffsetX), maxOffsetX);
            cropOffsetY = Math.min(Math.max(cropOffsetY, -maxOffsetY), maxOffsetY);
            const left = ((frameWidth - drawWidth) / 2) + cropOffsetX;
            const top = ((frameHeight - drawHeight) / 2) + cropOffsetY;

            cropImage.style.width = drawWidth + 'px';
            cropImage.style.height = drawHeight + 'px';
            cropImage.style.transform = 'translate(calc(-50% + ' + cropOffsetX + 'px), calc(-50% + ' + cropOffsetY + 'px))';

            cropCanvas.width = frameWidth;
            cropCanvas.height = frameHeight;
            const ctx = cropCanvas.getContext('2d');
            ctx.clearRect(0, 0, cropCanvas.width, cropCanvas.height);
            ctx.drawImage(cropSourceImage, left, top, drawWidth, drawHeight);
        }

        function optimizeImageBlob(blob, fileName) {
            return new Promise(function (resolve) {
                const image = new Image();
                const sourceUrl = URL.createObjectURL(blob);

                image.onload = function () {
                    const maxSize = 960;
                    const longestSide = Math.max(image.width, image.height);
                    const ratio = longestSide > maxSize ? maxSize / longestSide : 1;
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(image.width * ratio);
                    canvas.height = Math.round(image.height * ratio);
                    const context = canvas.getContext('2d', { alpha: false });
                    context.imageSmoothingEnabled = true;
                    context.imageSmoothingQuality = 'high';
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob(function (optimizedBlob) {
                        URL.revokeObjectURL(sourceUrl);

                        if (!optimizedBlob) {
                            resolve(new File([blob], fileName, { type: 'image/jpeg' }));
                            return;
                        }

                        const safeName = fileName.replace(/\.[^.]+$/, '') + '.jpg';
                        resolve(new File([optimizedBlob], safeName, { type: 'image/jpeg' }));
                    }, 'image/jpeg', 0.82);
                };

                image.src = sourceUrl;
            });
        }

        async function applyCrop() {
            if (!cropSourceImage) {
                return;
            }

            setUploadStatus('Foto sedang dioptimalkan supaya upload lebih cepat...');

            cropCanvas.toBlob(async function (blob) {
                if (!blob) {
                    setUploadStatus('');
                    return;
                }

                const originalName = activeSourceInput && activeSourceInput.files && activeSourceInput.files[0]
                    ? activeSourceInput.files[0].name
                    : 'profile-photo.png';
                const croppedFile = await optimizeImageBlob(blob, originalName);
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                realInput.files = transfer.files;

                if (currentPreviewUrl) {
                    URL.revokeObjectURL(currentPreviewUrl);
                }

                const previewUrl = URL.createObjectURL(croppedFile);
                currentPreviewUrl = previewUrl;
                profilePreviewImage.src = previewUrl;
                profilePreview.classList.add('show');
                profilePhotoName.textContent = croppedFile.name;
                profilePhotoName.classList.remove('is-empty');
                syncActivePhoto(previewUrl);
                syncGlobalProfilePhoto(previewUrl);
                setUploadStatus('Preview siap. File sudah diperkecil agar upload lebih cepat.');
                if (activeSourceInput) {
                    activeSourceInput.value = '';
                }
                closeCropModal(false);
            }, 'image/png');
        }

        function captureCameraPhoto() {
            if (!cameraVideo.videoWidth || !cameraVideo.videoHeight) {
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = cameraVideo.videoWidth;
            canvas.height = cameraVideo.videoHeight;
            const context = canvas.getContext('2d');
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function (blob) {
                if (!blob) {
                    return;
                }

                const file = new File([blob], 'profile-camera.png', { type: 'image/png' });
                closeCameraModal();
                openCropModal(null, file);
            }, 'image/png');
        }

        openCameraButton.addEventListener('click', function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                cameraInput.click();
                return;
            }

            openCameraModal();
        });

        openGalleryButton.addEventListener('click', function () {
            galleryInput.click();
        });

        [cameraInput, galleryInput].forEach(function (input) {
            input.addEventListener('change', function () {
                if (input.files && input.files.length) {
                    latestOriginalFile = input.files[0];
                    openCropModal(input, input.files[0]);
                }
            });
        });

        profileRemove.addEventListener('click', resetPreview);
        profilePreviewImage.addEventListener('click', reopenCropFromPreview);
        activePhoto.addEventListener('click', reopenCropFromPreview);

        modalMask.addEventListener('click', function () {
            closeCameraModal();
            closeCropModal();
        });

        cropZoom.addEventListener('input', function () {
            cropScale = Number(cropZoom.value);
            cropZoomValue.textContent = Math.round(cropScale * 100) + '%';
            renderCrop();
        });

        cropFrame.addEventListener('pointerdown', function (event) {
            if (!cropSourceImage) {
                return;
            }

            cropDragging = true;
            dragStartX = event.clientX;
            dragStartY = event.clientY;
            dragOriginX = cropOffsetX;
            dragOriginY = cropOffsetY;
            cropFrame.classList.add('dragging');
            cropFrame.setPointerCapture(event.pointerId);
        });

        cropFrame.addEventListener('pointermove', function (event) {
            if (!cropDragging) {
                return;
            }

            cropOffsetX = dragOriginX + (event.clientX - dragStartX);
            cropOffsetY = dragOriginY + (event.clientY - dragStartY);
            renderCrop();
        });

        function stopDragging(event) {
            if (!cropDragging) {
                return;
            }

            cropDragging = false;
            cropFrame.classList.remove('dragging');
            if (event) {
                cropFrame.releasePointerCapture(event.pointerId);
            }
        }

        cropFrame.addEventListener('pointerup', stopDragging);
        cropFrame.addEventListener('pointercancel', stopDragging);

        document.getElementById('profileCameraClose').addEventListener('click', closeCameraModal);
        document.getElementById('profileCameraCancel').addEventListener('click', closeCameraModal);
        document.getElementById('profileCameraCapture').addEventListener('click', captureCameraPhoto);
        document.getElementById('profileCropClose').addEventListener('click', function () { closeCropModal(); });
        document.getElementById('profileCropCancel').addEventListener('click', function () { closeCropModal(); });
        document.getElementById('profileCropApply').addEventListener('click', applyCrop);

        window.handleProfileUpdateSuccess = function (data) {
            if (data.photo_url) {
                syncActivePhoto(data.photo_url);
                profilePreviewImage.src = data.photo_url;
                syncGlobalProfilePhoto(data.photo_url);
            }

            setUploadStatus(data.message || 'Foto profil berhasil disimpan.');
        };

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeCameraModal();
                closeCropModal();
            }
        });

        @if ($user->profile_photo_url)
            profilePreview.classList.add('show');
            profilePreviewImage.src = '{{ $user->profile_photo_url }}';
            profilePhotoName.textContent = 'Foto profil saat ini';
            profilePhotoName.classList.remove('is-empty');
        @endif

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
