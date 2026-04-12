@extends('layouts.admin')

@section('content')
@php($title = 'Scan Barcode')
@php($eyebrow = 'Khusus Superadmin')

<style>
    .scan-page{display:flex;flex-direction:column;gap:24px}
    .scan-shell{display:grid;grid-template-columns:minmax(340px,480px) minmax(0,1fr);gap:20px}
    .scan-card{background:var(--bg-card);border:1px solid var(--border);border-radius:22px;box-shadow:var(--shadow-sm)}
    .scan-card-main{padding:24px}
    .scan-title{font-family:'Playfair Display',serif;font-size:32px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
    .scan-sub{font-size:14px;color:var(--muted);margin-top:8px;line-height:1.7}
    .scan-stage{position:relative;overflow:hidden;border-radius:22px;background:#101714;border:1px solid var(--border);aspect-ratio:3/4}
    .scan-stage #reader{width:100%;height:100%}
    .scan-stage video{width:100%!important;height:100%!important;object-fit:cover;display:block;transform:scaleX(-1)}
    .scan-overlay{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none}
    .scan-frame{width:min(78%,280px);height:min(42%,160px);border:2px solid rgba(255,255,255,.88);border-radius:18px;box-shadow:0 0 0 999px rgba(0,0,0,.18)}
    .scan-hint{font-size:12px;color:var(--muted);line-height:1.6;margin-top:14px}
    .scan-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
    .scan-status{margin-top:14px;padding:12px 14px;border-radius:14px;background:var(--bg-soft);color:var(--muted);font-size:13px;line-height:1.6;border:1px solid var(--border)}
    .scan-status.error{background:var(--red-light);color:var(--red);border-color:rgba(196,69,54,.14)}
    .scan-status.success{background:var(--teal-light);color:var(--teal);border-color:rgba(45,134,89,.12)}
    .scan-manual{margin-top:18px;padding-top:18px;border-top:1px solid var(--border)}
    .scan-field-label{display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}
    .scan-result{padding:24px}
    .scan-empty{border:1px dashed var(--border-light);border-radius:18px;padding:42px 20px;text-align:center;color:var(--muted);background:var(--bg-soft)}
    .scan-book{display:grid;grid-template-columns:132px minmax(0,1fr);gap:18px}
    .scan-book-cover{width:132px;height:176px;border-radius:18px;overflow:hidden;background:linear-gradient(135deg,var(--accent),var(--accent-light));display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;font-weight:700}
    .scan-book-cover img{width:100%;height:100%;object-fit:cover}
    .scan-book-name{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:var(--fg);line-height:1.1}
    .scan-book-meta{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:18px}
    .scan-meta-item{padding:14px;border:1px solid var(--border);border-radius:16px;background:var(--bg-soft)}
    .scan-meta-label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
    .scan-meta-value{font-size:14px;color:var(--fg);font-weight:700;margin-top:6px;line-height:1.5}
    .scan-stock{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px}
    .scan-stock-chip{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff;border:1px solid var(--border);font-size:13px;font-weight:700;color:var(--fg)}
    .scan-desc{margin-top:18px;padding:16px;border-radius:16px;background:var(--bg-soft);border:1px solid var(--border);font-size:13px;color:var(--muted);line-height:1.7}
    .scan-source{display:inline-flex;align-items:center;gap:8px;margin-top:10px;padding:8px 12px;border-radius:999px;background:var(--bg-soft);border:1px solid var(--border);font-size:12px;font-weight:700;color:var(--fg)}
    @media (max-width:1040px){.scan-shell{grid-template-columns:1fr}}
    @media (max-width:640px){.scan-book{grid-template-columns:1fr}.scan-book-cover{width:120px;height:160px}.scan-book-meta{grid-template-columns:1fr}}
</style>

<div class="scan-page">
    <div class="member-toolbar">
        <div>
            <h1 class="scan-title">Scan Barcode Buku</h1>
            <div class="scan-sub">Arahkan kamera ke barcode buku. Kalau ISBN cocok dengan data, sistem langsung menampilkan cover, nama buku, penerbit, kategori, dan stok yang tersedia.</div>
        </div>
        <div class="member-badge"><i data-lucide="scan-line" class="w-3.5 h-3.5"></i> ISBN Scanner</div>
    </div>

    <div class="scan-shell">
        <div class="scan-card scan-card-main">
            <div class="scan-stage">
                <div id="reader"></div>
                <div class="scan-overlay">
                    <div class="scan-frame"></div>
                </div>
            </div>

            <div id="scanStatus" class="scan-status">Klik `Mulai Scan` untuk membuka kamera belakang dan mulai membaca barcode ISBN buku.</div>

            <div class="scan-actions">
                <button type="button" id="startScanButton" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold">Mulai Scan</button>
                <button type="button" id="stopScanButton" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold">Stop</button>
            </div>

            <div class="scan-hint">Scanner ini memakai library barcode yang lebih kompatibel di browser HP. Kalau kamera tetap tidak tersedia, kamu masih bisa pakai input manual ISBN di bawah.</div>

            <div class="scan-manual">
                <label class="scan-field-label" for="manualBarcode">Input Barcode / ISBN Manual</label>
                <div class="book-grid">
                    <input id="manualBarcode" class="form-input px-3 py-3 text-sm" placeholder="Contoh: 9786022917729">
                    <button type="button" id="manualLookupButton" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold">Cari Buku</button>
                </div>
            </div>
        </div>

        <div class="scan-card scan-result">
            <div id="scanResultEmpty" class="scan-empty">
                Detail buku akan muncul di sini setelah barcode berhasil dibaca.
            </div>

            <div id="scanResultCard" style="display:none;">
                <div class="scan-book">
                    <div id="scanBookCover" class="scan-book-cover">B</div>
                    <div>
                        <div id="scanBookName" class="scan-book-name">-</div>
                        <div id="scanBookAuthor" class="scan-sub" style="margin-top:8px;">-</div>
                        <div id="scanBookSource" class="scan-source" style="display:none;"></div>

                        <div class="scan-book-meta">
                            <div class="scan-meta-item">
                                <div class="scan-meta-label">Penerbit</div>
                                <div id="scanBookPublisher" class="scan-meta-value">-</div>
                            </div>
                            <div class="scan-meta-item">
                                <div class="scan-meta-label">Kategori</div>
                                <div id="scanBookCategory" class="scan-meta-value">-</div>
                            </div>
                            <div class="scan-meta-item">
                                <div class="scan-meta-label">Barcode</div>
                                <div id="scanBookBarcode" class="scan-meta-value">-</div>
                            </div>
                            <div class="scan-meta-item">
                                <div class="scan-meta-label">ISBN</div>
                                <div id="scanBookIsbn" class="scan-meta-value">-</div>
                            </div>
                            <div class="scan-meta-item">
                                <div class="scan-meta-label">Tahun Terbit</div>
                                <div id="scanBookYear" class="scan-meta-value">-</div>
                            </div>
                        </div>

                        <div class="scan-stock">
                            <div class="scan-stock-chip">Stok tersedia: <span id="scanBookStockAvailable">0</span></div>
                            <div class="scan-stock-chip">Stok total: <span id="scanBookStockTotal">0</span></div>
                        </div>
                    </div>
                </div>

                <div id="scanBookDescription" class="scan-desc" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
    const scanStatus = document.getElementById('scanStatus');
    const startScanButton = document.getElementById('startScanButton');
    const stopScanButton = document.getElementById('stopScanButton');
    const manualBarcode = document.getElementById('manualBarcode');
    const manualLookupButton = document.getElementById('manualLookupButton');
    const scanResultEmpty = document.getElementById('scanResultEmpty');
    const scanResultCard = document.getElementById('scanResultCard');
    const scanBookCover = document.getElementById('scanBookCover');
    const scanBookName = document.getElementById('scanBookName');
    const scanBookAuthor = document.getElementById('scanBookAuthor');
    const scanBookSource = document.getElementById('scanBookSource');
    const scanBookPublisher = document.getElementById('scanBookPublisher');
    const scanBookCategory = document.getElementById('scanBookCategory');
    const scanBookBarcode = document.getElementById('scanBookBarcode');
    const scanBookIsbn = document.getElementById('scanBookIsbn');
    const scanBookYear = document.getElementById('scanBookYear');
    const scanBookStockAvailable = document.getElementById('scanBookStockAvailable');
    const scanBookStockTotal = document.getElementById('scanBookStockTotal');
    const scanBookDescription = document.getElementById('scanBookDescription');

    let html5QrCode = null;
    let scannerRunning = false;
    let lastScannedCode = '';
    let lookupInFlight = false;

    function setScanStatus(message, type = '') {
        scanStatus.textContent = message;
        scanStatus.className = 'scan-status' + (type ? ' ' + type : '');
    }

    async function stopBarcodeStream() {
        if (!html5QrCode || !scannerRunning) {
            return;
        }

        try {
            await html5QrCode.stop();
        } catch (error) {
        }

        try {
            await html5QrCode.clear();
        } catch (error) {
        }

        scannerRunning = false;
    }

    async function startBarcodeScan() {
        if (!('mediaDevices' in navigator) || !navigator.mediaDevices.getUserMedia) {
            setScanStatus('Browser ini tidak mendukung akses kamera. Pakai input manual ISBN.', 'error');
            return;
        }

        if (!window.Html5Qrcode) {
            setScanStatus('Library scanner belum termuat. Coba refresh halaman lalu scan lagi.', 'error');
            return;
        }

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode('reader');
        }

        if (scannerRunning) {
            setScanStatus('Scanner sudah aktif. Arahkan barcode ke dalam frame.', '');
            return;
        }

        try {
            await html5QrCode.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: { width: 260, height: 120 },
                    aspectRatio: 1.333334,
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39
                    ]
                },
                function (decodedText) {
                    const rawValue = (decodedText || '').trim();

                    if (!rawValue || rawValue === lastScannedCode || lookupInFlight) {
                        return;
                    }

                    lastScannedCode = rawValue;
                    fetchBook(rawValue);
                },
                function () {
                }
            );

            scannerRunning = true;
            setScanStatus('Kamera aktif. Arahkan barcode ISBN buku ke dalam frame.', '');
        } catch (error) {
            setScanStatus('Kamera tidak bisa dibuka untuk scan. Pastikan izin kamera aktif dan coba pakai Chrome di HP.', 'error');
        }
    }

    async function fetchBook(code) {
        lookupInFlight = true;
        setScanStatus('Mencari data buku untuk barcode: ' + code, '');

        try {
            const url = new URL("{{ route('admin.books.lookup') }}", window.location.origin);
            url.searchParams.set('code', code);

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.found) {
                setScanStatus(data.message || 'Buku tidak ditemukan.', 'error');
                return;
            }

            renderBook(data.book);
            setScanStatus('Buku berhasil ditemukan dari barcode.', 'success');
        } catch (error) {
            setScanStatus('Terjadi error saat mengambil data buku.', 'error');
        } finally {
            lookupInFlight = false;
        }
    }

    function renderBook(book) {
        scanResultEmpty.style.display = 'none';
        scanResultCard.style.display = 'block';

        if (book.cover_url) {
            scanBookCover.innerHTML = '<img src="' + book.cover_url + '" alt="' + book.title.replace(/"/g, '&quot;') + '">';
        } else {
            scanBookCover.textContent = (book.title || 'B').charAt(0).toUpperCase();
        }

        scanBookName.textContent = book.title || '-';
        scanBookAuthor.textContent = book.author || '-';
        if (book.source === 'google') {
            scanBookSource.style.display = 'inline-flex';
            scanBookSource.textContent = 'Data dari Google Books, belum tersimpan di stok lokal';
        } else {
            scanBookSource.style.display = 'inline-flex';
            scanBookSource.textContent = 'Data dari koleksi perpustakaan';
        }
        scanBookPublisher.textContent = book.publisher || '-';
        scanBookCategory.textContent = book.category || '-';
        scanBookBarcode.textContent = book.barcode || '-';
        scanBookIsbn.textContent = book.isbn || '-';
        scanBookYear.textContent = book.published_year || '-';
        scanBookStockAvailable.textContent = book.stock_available ?? 0;
        scanBookStockTotal.textContent = book.stock_total ?? 0;

        if (book.description) {
            scanBookDescription.style.display = 'block';
            scanBookDescription.textContent = book.description;
        } else {
            scanBookDescription.style.display = 'none';
            scanBookDescription.textContent = '';
        }
    }

    startScanButton.addEventListener('click', function () {
        startBarcodeScan();
    });

    stopScanButton.addEventListener('click', async function () {
        await stopBarcodeStream();
        setScanStatus('Scan dihentikan. Klik `Mulai Scan` untuk membuka kamera lagi.', '');
    });

    manualLookupButton.addEventListener('click', function () {
        const code = manualBarcode.value.trim();

        if (!code) {
            setScanStatus('Masukkan barcode atau ISBN dulu.', 'error');
            return;
        }

        lastScannedCode = code;
        fetchBook(code);
    });

    manualBarcode.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            manualLookupButton.click();
        }
    });

    window.addEventListener('beforeunload', function () {
        stopBarcodeStream();
    });
</script>
@endsection
