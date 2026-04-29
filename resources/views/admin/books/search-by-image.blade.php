@extends('layouts.admin')

@section('title', 'Cari Buku dengan Foto')

@section('content')
<style>
    .image-search-shell{display:grid;grid-template-columns:minmax(320px,420px) minmax(0,1fr);gap:28px}
    .image-search-card,.image-results-card{background:var(--bg-card);border:1px solid var(--border-light);border-radius:24px;box-shadow:var(--shadow-sm)}
    .image-search-card{padding:28px;position:relative;overflow:hidden}
    .image-search-card:before{content:'';position:absolute;right:-90px;top:-80px;width:220px;height:220px;border-radius:999px;background:radial-gradient(circle,rgba(var(--accent-rgb),.12),transparent 70%)}
    .image-search-card > *,.image-results-card > *{position:relative;z-index:1}
    .image-search-title{font-family:'Playfair Display',serif;font-size:34px;font-weight:800;letter-spacing:-.03em;color:var(--fg)}
    .image-search-sub{margin-top:10px;font-size:15px;line-height:1.7;color:var(--muted)}
    .image-search-note{margin-top:16px;padding:14px 16px;border-radius:18px;background:#fff9f2;border:1px solid rgba(196,149,106,.22);font-size:13px;line-height:1.6;color:var(--fg)}
    .image-source-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px}
    .image-source-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:14px;border:1px solid var(--border);background:var(--bg-soft);color:var(--fg);font-size:13px;font-weight:700;cursor:pointer;transition:.2s}
    .image-source-btn:hover{border-color:var(--accent);background:#fff}
    .image-upload{margin-top:18px;display:flex;flex-direction:column;gap:12px}
    .image-upload input[type="file"]{display:none}
    .image-preview{display:none;align-items:center;justify-content:center;min-height:320px;border-radius:22px;border:1px dashed var(--border-light);background:linear-gradient(135deg,#fff, #fbf4eb);overflow:hidden}
    .image-preview.show{display:flex}
    .image-preview img{max-width:100%;max-height:420px;object-fit:contain}
    .image-file-name{font-size:13px;color:var(--muted)}
    .image-file-name.is-empty{display:none}
    .image-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}
    .image-action-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border-radius:14px;border:none;cursor:pointer;font-size:13px;font-weight:700;transition:.2s}
    .image-action-btn.primary{background:var(--accent);color:#fff;box-shadow:0 8px 20px rgba(196,149,106,.25)}
    .image-action-btn.primary:hover{background:var(--accent-light);transform:translateY(-1px)}
    .image-action-btn.secondary{background:#fff;border:1px solid var(--border-light);color:var(--fg)}
    .image-action-btn.secondary:hover{border-color:var(--accent);color:var(--accent)}
    .image-results-card{padding:28px}
    .image-results-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:20px}
    .image-results-title{font-family:'Playfair Display',serif;font-size:26px;font-weight:800;color:var(--fg)}
    .image-results-sub{font-size:13px;color:var(--muted);margin-top:5px}
    .image-result-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .image-result-card{border:1px solid var(--border-light);border-radius:20px;overflow:hidden;background:#fff}
    .image-result-cover{aspect-ratio:3/4;background:linear-gradient(135deg,#f7efe4,#fff);display:flex;align-items:center;justify-content:center}
    .image-result-cover img{width:100%;height:100%;object-fit:cover}
    .image-result-body{padding:16px}
    .image-result-title{font-size:16px;font-weight:800;color:var(--fg);line-height:1.35}
    .image-result-meta{font-size:13px;color:var(--muted);line-height:1.6;margin-top:8px}
    .image-result-score{display:inline-flex;align-items:center;gap:6px;margin-top:12px;padding:6px 10px;border-radius:999px;background:var(--gold-light);color:var(--gold);font-size:12px;font-weight:800}
    .image-result-link{display:inline-flex;align-items:center;justify-content:center;margin-top:14px;padding:10px 12px;border-radius:12px;border:1px solid var(--border-light);font-size:13px;font-weight:700;color:var(--fg);text-decoration:none}
    .image-result-link:hover{border-color:var(--accent);color:var(--accent)}
    .image-empty{border:1px dashed var(--border-light);border-radius:20px;background:var(--bg-soft);padding:28px;text-align:center;color:var(--muted);line-height:1.7}
    .image-progress{font-size:13px;color:var(--muted);margin-top:12px}
    .image-attribute-box{margin-top:16px;padding:14px 16px;border-radius:18px;background:#fffaf6;border:1px solid var(--border-light)}
    .image-attribute-box h3{font-size:13px;font-weight:800;color:var(--fg);margin:0 0 10px}
    .image-attribute-box ul{margin:0;padding-left:18px;color:var(--muted);font-size:13px;line-height:1.65}
    @media (max-width: 1024px){.image-search-shell{grid-template-columns:1fr}.image-result-list{grid-template-columns:1fr}}
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Cari Buku dengan Foto</h1>
            <p class="member-subtitle">Upload foto sampul, screenshot buku, atau jepretan kamera untuk mencocokkan dengan katalog perpustakaan.</p>
        </div>
        <a href="{{ route('admin.books.index') }}" class="member-badge" style="background:var(--bg-card);color:var(--fg);border:1px solid var(--border-light);text-decoration:none;">
            <i data-lucide="book-copy" class="w-4 h-4"></i> Kembali ke Buku
        </a>
    </div>

    <div class="image-search-shell">
        <section class="image-search-card">
            <div class="image-search-title">Unggah foto buku</div>
            <div class="image-search-sub">Sistem akan mencari sampul yang paling mirip. Kalau teks di foto terbaca, ISBN, judul, dan penulis juga dipakai sebagai bantuan pencarian.</div>

            <div class="image-search-note">
                Tips cepat: foto cover jangan terlalu gelap, usahakan penuh satu buku, dan hindari blur agar hasilnya lebih akurat.
            </div>

            <form id="book-image-search-form" class="image-upload">
                @csrf
                <input id="bookImageCameraInput" name="image" type="file" accept="image/*" capture="environment">
                <input id="bookImageGalleryInput" name="image" type="file" accept="image/*">
                <div style="position:relative;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <button type="button" class="image-source-btn" id="openSourceMenuBtn" style="width:48px;height:48px;padding:0;border-radius:14px;">
                        <i data-lucide="camera" class="w-5 h-5"></i>
                    </button>
                    <div id="sourceMenu" style="display:none;position:absolute;top:58px;left:0;z-index:20;min-width:210px;padding:10px;border:1px solid rgba(196,149,106,.18);border-radius:16px;background:#fff;box-shadow:0 16px 30px rgba(15,76,92,.12);">
                        <button type="button" class="image-source-btn" id="openCameraBtn" style="width:100%;justify-content:flex-start;margin-bottom:8px;">
                            <i data-lucide="camera" class="w-4 h-4"></i> Foto Langsung
                        </button>
                        <button type="button" class="image-source-btn" id="openGalleryBtn" style="width:100%;justify-content:flex-start;">
                            <i data-lucide="images" class="w-4 h-4"></i> Dari File / Galeri
                        </button>
                    </div>
                    <div class="image-search-sub" style="margin-top:0;">Klik ikon kamera untuk memilih cara unggah foto.</div>
                </div>

                <div class="image-preview" id="bookImagePreview">
                    <img id="bookImagePreviewImg" alt="Preview foto buku">
                </div>

                <div class="image-file-name is-empty" id="bookImageFileName">Belum ada file dipilih</div>

                <div class="image-actions">
                    <button type="submit" class="image-action-btn primary" id="searchImageButton">
                        <i data-lucide="search" class="w-4 h-4"></i> Cari Buku
                    </button>
                    <button type="button" class="image-action-btn secondary" id="clearImageButton">
                        <i data-lucide="x" class="w-4 h-4"></i> Hapus Pilihan
                    </button>
                </div>

                <div class="image-progress" id="uploadStatus"></div>
            </form>
        </section>

        <section class="image-results-card">
            <div class="image-results-head">
                <div>
                    <div class="image-results-title">Hasil Pencarian</div>
                    <div class="image-results-sub">Hanya buku yang benar-benar cocok akan ditampilkan. Kalau tidak ada, akan tampil buku tidak ditemukan.</div>
                </div>
                <div class="member-badge" style="background:var(--gold-light);color:var(--gold);">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Visual Match
                </div>
            </div>

            <div id="searchResults">
                <div class="image-empty">Unggah foto sampul buku untuk mulai mencari. Hasil yang cocok akan tampil di sini.</div>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('book-image-search-form');
        const cameraInput = document.getElementById('bookImageCameraInput');
        const galleryInput = document.getElementById('bookImageGalleryInput');
        const preview = document.getElementById('bookImagePreview');
        const previewImg = document.getElementById('bookImagePreviewImg');
        const fileName = document.getElementById('bookImageFileName');
        const status = document.getElementById('uploadStatus');
        const results = document.getElementById('searchResults');
        const searchButton = document.getElementById('searchImageButton');
        const openSourceMenuBtn = document.getElementById('openSourceMenuBtn');
        const sourceMenu = document.getElementById('sourceMenu');
        const openCameraBtn = document.getElementById('openCameraBtn');
        const openGalleryBtn = document.getElementById('openGalleryBtn');
        const clearButton = document.getElementById('clearImageButton');

        function resetPreview() {
            if (cameraInput) {
                cameraInput.value = '';
            }
            if (galleryInput) {
                galleryInput.value = '';
            }
            preview.classList.remove('show');
            previewImg.removeAttribute('src');
            fileName.textContent = 'Belum ada file dipilih';
            fileName.classList.add('is-empty');
        }

        function setPreview(file) {
            if (!file) {
                resetPreview();
                return;
            }

            const url = URL.createObjectURL(file);
            previewImg.src = url;
            preview.classList.add('show');
            fileName.textContent = file.name || 'foto-buku';
            fileName.classList.remove('is-empty');
        }

        if (cameraInput) {
            cameraInput.addEventListener('change', function () {
                const file = cameraInput.files && cameraInput.files[0] ? cameraInput.files[0] : null;
                if (galleryInput) {
                    galleryInput.value = '';
                }
                setPreview(file);
            });
        }

        if (galleryInput) {
            galleryInput.addEventListener('change', function () {
                const file = galleryInput.files && galleryInput.files[0] ? galleryInput.files[0] : null;
                if (cameraInput) {
                    cameraInput.value = '';
                }
                setPreview(file);
            });
        }

        openCameraBtn.addEventListener('click', function () {
            if (cameraInput) {
                cameraInput.click();
            }
            if (sourceMenu) {
                sourceMenu.style.display = 'none';
            }
        });

        openGalleryBtn.addEventListener('click', function () {
            if (galleryInput) {
                galleryInput.click();
            }
            if (sourceMenu) {
                sourceMenu.style.display = 'none';
            }
        });

        if (openSourceMenuBtn && sourceMenu) {
            openSourceMenuBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                sourceMenu.style.display = sourceMenu.style.display === 'block' ? 'none' : 'block';
            });
        }

        clearButton.addEventListener('click', function () {
            resetPreview();
            status.textContent = '';
            results.innerHTML = '<div class="image-empty">Unggah foto sampul buku untuk mulai mencari. Hasil yang cocok akan tampil di sini.</div>';
        });

        document.addEventListener('click', function (event) {
            if (!sourceMenu) {
                return;
            }

            const clickedInside = event.target.closest('#sourceMenu') || event.target.closest('#openSourceMenuBtn');
            if (!clickedInside) {
                sourceMenu.style.display = 'none';
            }
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const file = cameraInput && cameraInput.files && cameraInput.files[0]
                ? cameraInput.files[0]
                : (galleryInput && galleryInput.files && galleryInput.files[0]
                    ? galleryInput.files[0]
                    : null);

            if (!file) {
                status.textContent = 'Pilih gambar dulu sebelum mencari.';
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}');
            formData.append('image', file);
            searchButton.disabled = true;
            status.textContent = 'Memproses foto dan mencari kecocokan yang sama...';
            results.innerHTML = '<div class="image-empty">Sedang mencari buku yang sama...</div>';

            try {
                const response = await fetch('{{ route('admin.books.search-by-image') }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Pencarian gagal.');
                }

                const books = Array.isArray(data.books) ? data.books : [];
                if (books.length === 0) {
                    status.textContent = '';
                    results.innerHTML = '<div class="image-empty" style="margin-top:16px;">Buku tidak ditemukan.</div>';
                    return;
                }

                status.textContent = data.message || 'Pencarian selesai.';
                renderResults(data);
            } catch (error) {
                status.textContent = error.message || 'Terjadi kesalahan saat mencari buku.';
                results.innerHTML = '<div class="image-empty">Pencarian gagal. Coba foto yang lebih jelas.</div>';
            } finally {
                searchButton.disabled = false;
            }
        });

        function renderResults(data) {
            const books = Array.isArray(data.books) ? data.books : [];
            const attrs = data.extracted_attributes || {};
            let html = '';

            if (attrs.raw_text || attrs.isbn || (attrs.title_candidates && attrs.title_candidates.length) || (attrs.author_candidates && attrs.author_candidates.length)) {
                html += '<div class="image-attribute-box">';
                html += '<h3>Hasil ekstraksi dari foto</h3>';
                html += '<ul>';
                html += '<li><strong>ISBN:</strong> ' + escapeHtml(attrs.isbn || 'Tidak ditemukan') + '</li>';
                html += '<li><strong>Calon judul:</strong> ' + escapeHtml(Array.isArray(attrs.title_candidates) && attrs.title_candidates.length ? attrs.title_candidates.join(', ') : 'Tidak ada') + '</li>';
                html += '<li><strong>Calon penulis:</strong> ' + escapeHtml(Array.isArray(attrs.author_candidates) && attrs.author_candidates.length ? attrs.author_candidates.join(', ') : 'Tidak ada') + '</li>';
                html += '</ul>';
                html += '</div>';
            }

            if (!books.length) {
                html += '<div class="image-empty" style="margin-top:16px;">Buku tidak ditemukan.</div>';
                results.innerHTML = html;
                return;
            }

            html += '<div class="image-result-list" style="margin-top:16px;">';
            books.forEach(function (book) {
                const score = typeof book.match_score === 'number' ? book.match_score.toFixed(2) : book.match_score;
                html += '<article class="image-result-card">';
                html += '<div class="image-result-cover">';
                html += book.cover_image_url
                    ? '<img src="' + escapeAttr(book.cover_image_url) + '" alt="' + escapeAttr(book.title || 'Buku') + '">'
                    : '<div class="image-empty" style="border:none;background:transparent;padding:18px;">Tidak ada sampul</div>';
                html += '</div>';
                html += '<div class="image-result-body">';
                html += '<div class="image-result-title">' + escapeHtml(book.title || 'Judul buku') + '</div>';
                html += '<div class="image-result-meta">';
                html += 'Penulis: ' + escapeHtml(book.author || 'Tidak tersedia') + '<br>';
                html += 'ISBN: ' + escapeHtml(book.isbn || 'Tidak tersedia') + '<br>';
                html += 'Kategori: ' + escapeHtml(book.category && book.category.name ? book.category.name : 'Tidak ada');
                html += '</div>';
                html += '<div class="image-result-score">Skor kecocokan ' + escapeHtml(String(score)) + '%</div>';
                html += '<div>';
                html += '<a href="{{ route('admin.books.index') }}" class="image-result-link">Lihat di daftar buku</a>';
                html += '</div>';
                html += '</div>';
                html += '</article>';
            });
            html += '</div>';

            results.innerHTML = html;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeAttr(value) {
            return escapeHtml(value).replace(/`/g, '&#096;');
        }
    });
</script>
@endsection
