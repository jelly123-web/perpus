<script>
    (function () {
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
        let settingAutoSaveTimer = null;
        let settingCurrentBindings = null;

        function buildSettingBindings() {
            const form = document.querySelector('.js-setting-form');
            if (!form || form.dataset.settingBound === '1') {
                return null;
            }

            const uploadRoot = document.getElementById('settingLogoUploadRoot');
            if (!uploadRoot) {
                return null;
            }

            form.dataset.settingBound = '1';

            return {
                form,
                appNameInput: document.getElementById('app_name'),
                appColorInput: document.getElementById('app_color'),
                appLogoInput: document.getElementById('app_logo'),
                removeLogoInput: document.getElementById('remove_logo'),
                settingPreviewName: document.getElementById('settingPreviewName'),
                settingShell: document.querySelector('.setting-shell'),
                settingColorText: document.getElementById('settingColorText'),
                settingLogoName: document.getElementById('settingLogoName'),
                settingPreviewLogo: document.getElementById('settingPreviewLogo'),
                settingPreviewIcon: document.getElementById('settingPreviewIcon'),
                settingLogoPreviewWrap: document.getElementById('settingLogoPreviewWrap'),
                settingLogoPreviewImage: document.getElementById('settingLogoPreviewImage'),
                settingLogoRemove: document.getElementById('settingLogoRemove'),
                settingUploadRoot: uploadRoot,
                settingCameraInput: uploadRoot.querySelector('.js-setting-camera-input'),
                settingGalleryInput: uploadRoot.querySelector('.js-setting-gallery-input'),
                settingOpenCameraButton: uploadRoot.querySelector('.js-setting-open-camera'),
                settingOpenGalleryButton: uploadRoot.querySelector('.js-setting-open-gallery'),
                settingModalMask: document.getElementById('settingModalMask'),
                settingCameraModal: document.getElementById('settingCameraModal'),
                settingCameraVideo: document.getElementById('settingCameraVideo'),
                settingCropModal: document.getElementById('settingCropModal'),
                settingCropFrame: document.getElementById('settingCropFrame'),
                settingCropImage: document.getElementById('settingCropImage'),
                settingCropCanvas: document.getElementById('settingCropCanvas'),
                settingCropZoom: document.getElementById('settingCropZoom'),
                settingCropZoomValue: document.getElementById('settingCropZoomValue'),
                settingBrandLogo: document.getElementById('settingBrandLogo'),
                settingSaveState: form.querySelector('.js-setting-save-state')
            };
        }

        function setSettingSaveState(message, tone) {
            if (!settingCurrentBindings?.settingSaveState) {
                return;
            }

            const tones = {
                muted: 'var(--muted)',
                success: 'var(--teal)',
                error: 'var(--red)'
            };

            settingCurrentBindings.settingSaveState.textContent = message;
            settingCurrentBindings.settingSaveState.style.color = tones[tone] || tones.muted;
        }

        function requestSettingAutoSave(delay = 500) {
            if (!settingCurrentBindings?.form) {
                return;
            }

            window.clearTimeout(settingAutoSaveTimer);
            setSettingSaveState('Menyimpan perubahan...', 'muted');
            settingAutoSaveTimer = window.setTimeout(function () {
                settingCurrentBindings.form.requestSubmit();
            }, delay);
        }

        function syncSettingPreview(previewUrl, fileName) {
            if (!settingCurrentBindings) {
                return;
            }

            settingCurrentBindings.removeLogoInput.value = '0';
            settingCurrentBindings.settingLogoName.textContent = fileName;
            settingCurrentBindings.settingLogoPreviewImage.src = previewUrl;
            settingCurrentBindings.settingLogoPreviewWrap.classList.add('show');
            settingCurrentBindings.settingPreviewLogo.src = previewUrl;
            settingCurrentBindings.settingPreviewLogo.style.display = 'block';
            settingCurrentBindings.settingBrandLogo.classList.add('has-image');

            if (settingCurrentBindings.settingPreviewIcon) {
                settingCurrentBindings.settingPreviewIcon.style.display = 'none';
            }
        }

        function resetSettingUpload(autoSubmit = true) {
            if (!settingCurrentBindings) {
                return;
            }

            settingCurrentBindings.appLogoInput.value = '';
            settingCurrentBindings.settingCameraInput.value = '';
            settingCurrentBindings.settingGalleryInput.value = '';
            settingCurrentBindings.removeLogoInput.value = '1';
            settingCurrentBindings.settingLogoName.textContent = 'Tidak ada file baru dipilih';
            settingCurrentBindings.settingLogoPreviewWrap.classList.remove('show');
            settingCurrentBindings.settingLogoPreviewImage.removeAttribute('src');
            settingCurrentBindings.settingPreviewLogo.style.display = 'none';
            settingCurrentBindings.settingPreviewLogo.removeAttribute('src');
            settingCurrentBindings.settingBrandLogo.classList.remove('has-image');

            if (settingCurrentBindings.settingPreviewIcon) {
                settingCurrentBindings.settingPreviewIcon.style.display = 'block';
            }

            if (autoSubmit) {
                requestSettingAutoSave(0);
            }
        }

        function openSettingCropModal(sourceInput, file) {
            if (!settingCurrentBindings) {
                return;
            }

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
                settingCurrentBindings.settingCropZoom.value = '1';
                settingCurrentBindings.settingCropZoomValue.textContent = '100%';
                settingCurrentBindings.settingCropImage.src = settingCropSourceUrl;
                renderSettingCrop();
                settingCurrentBindings.settingModalMask.classList.add('show');
                settingCurrentBindings.settingCropModal.classList.add('show');
                settingCurrentBindings.settingCropModal.setAttribute('aria-hidden', 'false');
            };
            settingCropSourceImage.src = settingCropSourceUrl;
        }

        function updateSettingCropFrameSize(imageWidth, imageHeight) {
            if (!settingCurrentBindings) {
                return;
            }

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

            settingCurrentBindings.settingCropFrame.style.setProperty('--setting-crop-frame-width', settingCropFrameWidth + 'px');
            settingCurrentBindings.settingCropFrame.style.setProperty('--setting-crop-frame-height', settingCropFrameHeight + 'px');
            document.documentElement.style.setProperty('--setting-crop-preview-ratio', settingCropFrameWidth + ' / ' + settingCropFrameHeight);
            settingCurrentBindings.settingCropCanvas.width = settingCropFrameWidth;
            settingCurrentBindings.settingCropCanvas.height = settingCropFrameHeight;
        }

        function closeSettingCropModal(resetInput = true) {
            if (!settingCurrentBindings) {
                return;
            }

            settingCurrentBindings.settingCropModal.classList.remove('show');
            settingCurrentBindings.settingCropModal.setAttribute('aria-hidden', 'true');

            if (!settingCurrentBindings.settingCameraModal.classList.contains('show')) {
                settingCurrentBindings.settingModalMask.classList.remove('show');
            }

            if (resetInput && settingActiveSourceInput) {
                settingActiveSourceInput.value = '';
            }
        }

        async function openSettingCameraModal() {
            if (!settingCurrentBindings) {
                return;
            }

            try {
                settingCameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'environment' }
                    },
                    audio: false
                });

                settingCurrentBindings.settingCameraVideo.srcObject = settingCameraStream;
                settingCurrentBindings.settingModalMask.classList.add('show');
                settingCurrentBindings.settingCameraModal.classList.add('show');
                settingCurrentBindings.settingCameraModal.setAttribute('aria-hidden', 'false');
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

            if (settingCurrentBindings?.settingCameraVideo) {
                settingCurrentBindings.settingCameraVideo.srcObject = null;
            }
        }

        function closeSettingCameraModal() {
            if (!settingCurrentBindings) {
                return;
            }

            settingCurrentBindings.settingCameraModal.classList.remove('show');
            settingCurrentBindings.settingCameraModal.setAttribute('aria-hidden', 'true');
            stopSettingCameraStream();

            if (!settingCurrentBindings.settingCropModal.classList.contains('show')) {
                settingCurrentBindings.settingModalMask.classList.remove('show');
            }
        }

        function renderSettingCrop() {
            if (!settingCropSourceImage || !settingCurrentBindings) {
                return;
            }

            const frameWidth = settingCurrentBindings.settingCropFrame.clientWidth || settingCropFrameWidth;
            const frameHeight = settingCurrentBindings.settingCropFrame.clientHeight || settingCropFrameHeight;
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

            settingCurrentBindings.settingCropImage.style.width = drawWidth + 'px';
            settingCurrentBindings.settingCropImage.style.height = drawHeight + 'px';
            settingCurrentBindings.settingCropImage.style.transform = 'translate(calc(-50% + ' + settingCropOffsetX + 'px), calc(-50% + ' + settingCropOffsetY + 'px))';

            const ctx = settingCurrentBindings.settingCropCanvas.getContext('2d');
            ctx.clearRect(0, 0, settingCurrentBindings.settingCropCanvas.width, settingCurrentBindings.settingCropCanvas.height);
            ctx.drawImage(settingCropSourceImage, left, top, drawWidth, drawHeight);
        }

        function applySettingCrop() {
            if (!settingCropSourceImage || !settingCurrentBindings) {
                return;
            }

            settingCurrentBindings.settingCropCanvas.toBlob(function (blob) {
                if (!blob || !settingCurrentBindings) {
                    return;
                }

                const originalName = settingActiveSourceInput && settingActiveSourceInput.files && settingActiveSourceInput.files[0]
                    ? settingActiveSourceInput.files[0].name
                    : 'app-logo.png';
                const croppedFile = new File([blob], originalName, { type: 'image/png' });
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                settingCurrentBindings.appLogoInput.files = transfer.files;

                const previewUrl = URL.createObjectURL(blob);
                syncSettingPreview(previewUrl, croppedFile.name);

                if (settingActiveSourceInput) {
                    settingActiveSourceInput.value = '';
                }

                closeSettingCropModal(false);
                requestSettingAutoSave(0);
            }, 'image/png');
        }

        function captureSettingCameraPhoto() {
            if (!settingCurrentBindings?.settingCameraVideo.videoWidth || !settingCurrentBindings.settingCameraVideo.videoHeight) {
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = settingCurrentBindings.settingCameraVideo.videoWidth;
            canvas.height = settingCurrentBindings.settingCameraVideo.videoHeight;
            const context = canvas.getContext('2d');
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(settingCurrentBindings.settingCameraVideo, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function (blob) {
                if (!blob) {
                    return;
                }

                const file = new File([blob], 'camera-logo.png', { type: 'image/png' });
                closeSettingCameraModal();
                openSettingCropModal(null, file);
            }, 'image/png');
        }

        function stopSettingDrag(event) {
            if (!settingCropDragging || !settingCurrentBindings) {
                return;
            }

            settingCropDragging = false;
            settingCurrentBindings.settingCropFrame.classList.remove('dragging');

            if (event) {
                settingCurrentBindings.settingCropFrame.releasePointerCapture(event.pointerId);
            }
        }

        function bindSettingPage() {
            const bindings = buildSettingBindings();
            if (!bindings) {
                return;
            }

            settingCurrentBindings = bindings;

            bindings.appNameInput.addEventListener('input', function () {
                bindings.settingPreviewName.textContent = bindings.appNameInput.value.trim() || 'LibraVault';
                requestSettingAutoSave();
            });

            bindings.appColorInput.addEventListener('input', function () {
                bindings.settingShell.style.setProperty('--preview-color', bindings.appColorInput.value);
                bindings.settingColorText.textContent = bindings.appColorInput.value.toUpperCase();
            });

            bindings.appColorInput.addEventListener('change', function () {
                requestSettingAutoSave(0);
            });

            bindings.settingOpenCameraButton.addEventListener('click', function () {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    bindings.settingCameraInput.click();
                    return;
                }

                openSettingCameraModal();
            });

            bindings.settingOpenGalleryButton.addEventListener('click', function () {
                bindings.settingGalleryInput.click();
            });

            [bindings.settingCameraInput, bindings.settingGalleryInput].forEach(function (input) {
                input.addEventListener('change', function () {
                    if (input.files && input.files.length) {
                        openSettingCropModal(input, input.files[0]);
                    }
                });
            });

            bindings.settingLogoRemove.addEventListener('click', function () {
                resetSettingUpload(true);
            });

            bindings.settingModalMask.addEventListener('click', function () {
                closeSettingCameraModal();
                closeSettingCropModal();
            });

            bindings.settingCropZoom.addEventListener('input', function () {
                settingCropScale = Number(bindings.settingCropZoom.value);
                bindings.settingCropZoomValue.textContent = Math.round(settingCropScale * 100) + '%';
                renderSettingCrop();
            });

            bindings.settingCropFrame.addEventListener('pointerdown', function (event) {
                if (!settingCropSourceImage) {
                    return;
                }

                settingCropDragging = true;
                settingDragStartX = event.clientX;
                settingDragStartY = event.clientY;
                settingDragOriginX = settingCropOffsetX;
                settingDragOriginY = settingCropOffsetY;
                bindings.settingCropFrame.classList.add('dragging');
                bindings.settingCropFrame.setPointerCapture(event.pointerId);
            });

            bindings.settingCropFrame.addEventListener('pointermove', function (event) {
                if (!settingCropDragging) {
                    return;
                }

                settingCropOffsetX = settingDragOriginX + (event.clientX - settingDragStartX);
                settingCropOffsetY = settingDragOriginY + (event.clientY - settingDragStartY);
                renderSettingCrop();
            });

            bindings.settingCropFrame.addEventListener('pointerup', stopSettingDrag);
            bindings.settingCropFrame.addEventListener('pointercancel', stopSettingDrag);

            bindings.form.addEventListener('submit', function () {
                window.clearTimeout(settingAutoSaveTimer);
                setSettingSaveState('Menyimpan perubahan...', 'muted');
            });
        }

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

        document.addEventListener('async:refreshed', function () {
            bindSettingPage();
            setSettingSaveState('Perubahan berhasil disimpan otomatis.', 'success');
        });

        document.addEventListener('async:form-success', function (event) {
            if (!event.detail?.form?.classList?.contains('js-setting-form')) {
                return;
            }

            setSettingSaveState('Perubahan berhasil disimpan otomatis.', 'success');
        });

        document.addEventListener('async:form-error', function (event) {
            if (!event.detail?.form?.classList?.contains('js-setting-form')) {
                return;
            }

            setSettingSaveState(event.detail?.error?.message || 'Perubahan gagal disimpan.', 'error');
        });

        bindSettingPage();

        if (window.lucide) {
            window.lucide.createIcons();
        }
    })();
</script>
