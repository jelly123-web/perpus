@extends('layouts.admin')

@section('content')
@php($title = 'Kelola Data Buku')
@php($eyebrow = 'Khusus Superadmin')

<style>
    .book-shell{display:grid;grid-template-columns:minmax(320px,430px) minmax(0,1fr);gap:20px}
    .book-add{position:relative;overflow:hidden;border-radius:20px;padding:24px;background:var(--bg-card);border:1px solid var(--border);box-shadow:var(--shadow-sm)}
    .book-add:before{content:'';position:absolute;right:-50px;top:-50px;width:170px;height:170px;border-radius:999px;background:radial-gradient(circle,rgba(13,155,106,.10),transparent 70%)}
    .book-add > *{position:relative;z-index:1}
    .book-add-title{font-family:'Playfair Display',serif;font-size:34px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
    .book-card{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:20px 20px 12px;box-shadow:var(--shadow-sm)}
    .book-list-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
    .book-list-title{font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:var(--fg)}
    .book-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 6px;border-top:1px solid var(--border)}
    .book-row:first-child{border-top:none}
    .book-row-main{display:flex;align-items:center;gap:14px;min-width:0}
    .book-cover-chip{width:50px;height:64px;border-radius:14px;background:linear-gradient(135deg,#2e6f7c,#5ab6b0);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;font-weight:700;flex-shrink:0}
    .book-row-meta{min-width:0}
    .book-row-title{font-size:15px;font-weight:700;color:var(--fg)}
    .book-row-sub{font-size:13px;color:var(--muted);margin-top:3px}
    .book-row-sub2{font-size:12px;color:var(--dim);margin-top:3px}
    .book-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .btn-danger-soft{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:var(--red);border:1px solid rgba(209,67,67,.18);border-radius:10px;padding:10px 14px;font-size:12px;font-weight:700;cursor:pointer;transition:.2s}
    .btn-danger-soft:hover{background:var(--red-light)}
    .book-empty{border:1px dashed var(--border-light);border-radius:18px;padding:40px 20px;text-align:center;color:var(--muted);background:var(--bg-soft)}
    .book-drawer-mask{position:fixed;inset:0;background:rgba(8,15,12,.28);opacity:0;pointer-events:none;transition:opacity .28s ease;z-index:70}
    .book-drawer-mask.show{opacity:1;pointer-events:auto}
    .book-drawer{position:fixed;top:0;right:0;width:min(560px,100vw);height:100vh;background:var(--bg-raised);box-shadow:-12px 0 40px rgba(0,0,0,.12);transform:translateX(100%);transition:transform .32s cubic-bezier(.4,0,.2,1);z-index:80;display:flex;flex-direction:column}
    .book-drawer.open{transform:translateX(0)}
    .book-drawer-head{padding:22px 22px 18px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .book-drawer-title{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--fg)}
    .book-drawer-sub{font-size:13px;color:var(--muted);margin-top:6px;line-height:1.6}
    .book-drawer-close{width:40px;height:40px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--muted);cursor:pointer}
    .book-drawer-body{padding:20px 22px 24px;overflow-y:auto}
    .book-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .book-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
    .book-category-box{padding:14px;border:1px solid var(--border);border-radius:16px;background:#fffaf6}
    .book-category-title{font-size:12px;font-weight:700;color:var(--fg);margin-bottom:10px;text-transform:uppercase;letter-spacing:.08em}
    .book-category-box .btn-soft{margin-top:8px}
    .book-actions-bottom{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
    .cover-upload{display:flex;flex-direction:column;gap:12px}
    .cover-upload input[type="file"]{display:none}
    .upload-source-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .upload-source-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:14px;border:1px solid var(--border);background:var(--bg-soft);color:var(--fg);font-size:13px;font-weight:700;cursor:pointer;transition:.2s}
    .upload-source-btn:hover{border-color:var(--accent);background:#fff}
    .upload-selected-name{font-size:13px;color:var(--muted);padding:2px 2px 0}
    .upload-selected-name.is-empty{display:none}
    .upload-preview{display:none;position:relative;margin-top:12px;width:132px}
    .upload-preview.show{display:block}
    .upload-preview img{width:132px;height:176px;border-radius:16px;object-fit:cover;border:1px solid var(--border);background:#fff}
    .upload-remove{position:absolute;top:8px;right:8px;width:28px;height:28px;border:none;border-radius:999px;background:rgba(0,0,0,.68);color:#fff;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center}
    .crop-mask{position:fixed;inset:0;background:rgba(8,15,12,.55);opacity:0;pointer-events:none;transition:opacity .25s ease;z-index:120}
    .crop-mask.show{opacity:1;pointer-events:auto}
    .crop-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%) scale(.96);width:min(760px,calc(100vw - 24px));background:var(--bg-raised);border:1px solid var(--border);border-radius:24px;box-shadow:var(--shadow-lg);z-index:130;opacity:0;pointer-events:none;transition:.25s ease;padding:20px}
    .crop-modal.show{opacity:1;pointer-events:auto;transform:translate(-50%,-50%) scale(1)}
    .crop-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
    .crop-title{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--fg)}
    .crop-stage-wrap{display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:18px}
    .crop-stage{display:flex;align-items:center;justify-content:center;min-height:430px;background:var(--bg-soft);border:1px solid var(--border);border-radius:20px;padding:16px}
    .crop-frame{position:relative;width:300px;height:400px;border-radius:18px;overflow:hidden;background:#efe8de;box-shadow:0 0 0 1px rgba(0,0,0,.04);touch-action:none;cursor:grab}
    .crop-frame.dragging{cursor:grabbing}
    .crop-frame img{position:absolute;left:50%;top:50%;max-width:none;user-select:none;pointer-events:none}
    .crop-side{display:flex;flex-direction:column;gap:14px}
    .crop-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
    .crop-range{width:100%}
    .crop-range-value{font-size:12px;color:var(--muted);margin-top:6px}
    .crop-preview{width:150px;height:200px;border-radius:16px;overflow:hidden;border:1px solid var(--border);background:#fff}
    .crop-preview canvas{width:100%;height:100%;display:block}
    .crop-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:18px}
    .crop-hint{font-size:12px;color:var(--muted);line-height:1.6}
    .camera-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%) scale(.96);width:min(560px,calc(100vw - 24px));max-height:calc(100vh - 24px);overflow-y:auto;background:var(--bg-raised);border:1px solid var(--border);border-radius:24px;box-shadow:var(--shadow-lg);z-index:130;opacity:0;pointer-events:none;transition:.25s ease;padding:20px}
    .camera-modal.show{opacity:1;pointer-events:auto;transform:translate(-50%,-50%) scale(1)}
    .camera-preview{position:relative;overflow:hidden;border-radius:20px;background:#111;aspect-ratio:3/4;border:1px solid var(--border)}
    .camera-preview video{width:100%;height:100%;object-fit:cover;display:block;transform:scaleX(-1)}
    .camera-help{font-size:12px;color:var(--muted);line-height:1.6;margin-top:12px}
    .camera-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px;flex-wrap:wrap}
    @media (max-width:820px){.crop-stage-wrap{grid-template-columns:1fr}.crop-side{order:-1}.crop-preview{width:120px;height:160px}}
    @media (max-width:1100px){.book-shell{grid-template-columns:1fr}}
    @media (max-width:768px){.book-grid,.book-grid-3,.upload-source-actions{grid-template-columns:1fr}.book-row{flex-direction:column;align-items:flex-start}.book-actions{justify-content:flex-start}}
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Kelola Data Buku</h1>
        </div>
        <div class="member-badge"><i data-lucide="book-copy" class="w-3.5 h-3.5"></i> Akses superadmin</div>
    </div>

    <section id="bookStatsWrap" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--accent);color:#fff;"><i data-lucide="book-copy" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $bookStats['total'] }}</div><div class="member-mini-label">Total Buku</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="package" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $bookStats['stock_total'] }}</div><div class="member-mini-label">Total Stok Buku</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="package-check" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $bookStats['stock_available'] }}</div><div class="member-mini-label">Stok Tersedia</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--orange-light);color:var(--orange);"><i data-lucide="tags" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $bookStats['categories'] }}</div><div class="member-mini-label">Kategori Aktif</div></div>
        </div>
    </section>

    <div class="book-shell">
        <div class="book-add">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="book-add-title">Tambah Buku</div>
                </div>
                <div class="member-badge"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Buku Baru</div>
            </div>

            <form id="createBookForm" method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data" class="space-y-3 mt-5" data-async="true" data-reset-on-success="true" data-success-call="resetCreateBookFormState" data-refresh-targets="#bookStatsWrap,#bookListWrap">
                @csrf
                <input name="title" class="form-input px-3 py-3 text-sm" placeholder="Judul buku" required>
                <div class="cover-upload js-cover-upload">
                    <input type="file" name="cover_image" accept="image/*" class="js-file-upload">
                    <input type="file" accept="image/*" capture="environment" class="js-camera-input">
                    <input type="file" accept="image/*" class="js-gallery-input">
                    <div class="upload-source-actions">
                        <button type="button" class="upload-source-btn js-open-camera">Foto Langsung</button>
                        <button type="button" class="upload-source-btn js-open-gallery">Pilih dari Galeri</button>
                    </div>
                    <div class="upload-selected-name file-upload-name is-empty">Belum ada file dipilih</div>
                    <div class="upload-preview js-upload-preview">
                        <img alt="Preview cover">
                        <button type="button" class="upload-remove js-upload-remove">X</button>
                    </div>
                </div>
                <div class="book-grid">
                    <input name="author" class="form-input px-3 py-3 text-sm" placeholder="Nama penulis" required>
                    <input name="publisher" class="form-input px-3 py-3 text-sm" placeholder="Penerbit">
                </div>
                <div class="book-grid">
                    <input name="isbn" class="form-input px-3 py-3 text-sm" placeholder="ISBN">
                    <input name="place_of_publication" class="form-input px-3 py-3 text-sm" placeholder="Tempat terbit">
                </div>
                <div class="book-grid">
                    <input name="published_year" type="number" min="1901" max="{{ now()->addYear()->format('Y') }}" class="form-input px-3 py-3 text-sm" placeholder="Tahun terbit">
                    <select id="createBookCategory" name="category_id" class="form-select px-3 py-3 text-sm">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="book-category-box">
                    <div class="book-category-title">Tambah Kategori Baru</div>
                    <div class="space-y-4">
                        <input id="inlineCategoryName" class="form-input px-3 py-3 text-sm" placeholder="Nama kategori baru">
                        <button id="inlineCategoryButton" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold w-full" type="button">Tambah Kategori</button>
                    </div>
                </div>
                <div class="book-grid">
                    <input type="number" name="stock_total" class="form-input px-3 py-3 text-sm" placeholder="Stok total" required>
                    <div></div>
                </div>
                <textarea name="description" class="form-textarea px-3 py-3 text-sm" rows="4" placeholder="Deskripsi buku"></textarea>
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Simpan Buku</button>
            </form>

            <div class="book-category-box" style="margin-top:18px;">
                <div class="book-category-title">Usulan Pengadaan Buku</div>
                <form method="POST" action="{{ route('admin.books.procurements.store') }}" class="space-y-4" data-async="true" data-reset-on-success="true" data-refresh-targets="#procurementListWrap">
                    @csrf
                    <input name="title" class="form-input px-3 py-3 text-sm" placeholder="Judul buku usulan" required>
                    <input name="author" class="form-input px-3 py-3 text-sm" placeholder="Penulis" required>
                    <div class="book-grid">
                        <input name="isbn" class="form-input px-3 py-3 text-sm" placeholder="ISBN">
                        <input name="publisher" class="form-input px-3 py-3 text-sm" placeholder="Penerbit">
                    </div>
                    <div class="book-grid">
                        <input name="published_year" type="number" min="1901" max="{{ now()->addYear()->format('Y') }}" class="form-input px-3 py-3 text-sm" placeholder="Tahun terbit">
                        <input name="quantity" type="number" min="1" class="form-input px-3 py-3 text-sm" placeholder="Jumlah usulan" required>
                    </div>
                    <select name="category_id" class="form-select px-3 py-3 text-sm">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <textarea name="notes" class="form-textarea px-3 py-3 text-sm" rows="3" placeholder="Catatan usulan pengadaan"></textarea>
                    <button class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold w-full" type="submit">Kirim Usulan ke Kepsek</button>
                </form>
            </div>
        </div>

        <div id="bookListWrap" class="book-card">
            <div class="book-list-head">
                <div class="book-list-title">Daftar Buku</div>
                <div class="member-badge"><i data-lucide="panel-right-open" class="w-3.5 h-3.5"></i> Edit via drawer</div>
            </div>

            @if ($books->count())
                @foreach ($books as $book)
                    <div class="book-row">
                        <div class="book-row-main">
                            @if ($book->cover_image)
                                <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}" class="book-cover-chip" style="object-fit:cover;">
                            @else
                                <div class="book-cover-chip">{{ strtoupper(substr($book->title, 0, 1)) }}</div>
                            @endif
                            <div class="book-row-meta">
                                <div class="book-row-title">{{ $book->title }}</div>
                                <div class="book-row-sub">{{ $book->author }}</div>
                                <div class="book-row-sub2">Stok {{ $book->stock_available }}/{{ $book->stock_total }} | ISBN {{ $book->isbn ?: '-' }}</div>
                            </div>
                        </div>
                        <div class="book-actions">
                            <button
                                type="button"
                                class="btn-primary rounded-xl px-4 py-2 text-xs font-semibold js-edit-book"
                                data-id="{{ $book->id }}"
                                data-title="{{ $book->title }}"
                                data-author="{{ $book->author }}"
                                data-publisher="{{ $book->publisher }}"
                                data-place="{{ $book->place_of_publication }}"
                                data-isbn="{{ $book->isbn }}"
                                data-published-year="{{ $book->published_year }}"
                                data-stock-total="{{ $book->stock_total }}"
                                data-stock-available="{{ $book->stock_available }}"
                                data-category-id="{{ $book->category_id }}"
                                data-description="{{ $book->description }}"
                                data-cover-url="{{ $book->cover_image ? asset('storage/'.$book->cover_image) : '' }}"
                            >Edit</button>
                            <form method="POST" action="{{ route('admin.books.destroy', $book) }}" data-async="true" data-confirm="Hapus buku ini?" data-remove-closest=".book-row" data-refresh-targets="#bookStatsWrap,#bookListWrap">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger-soft" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4">{{ $books->links() }}</div>
            @else
                <div class="book-empty">
                    <div class="text-lg font-semibold text-slate2-900">Belum ada data buku</div>
                    <div class="mt-2 text-sm">Buku baru akan muncul di sini setelah ditambahkan oleh super admin.</div>
                </div>
            @endif
        </div>
    </div>

    <div id="procurementListWrap" class="book-card" style="margin-top:20px;">
        <div class="book-list-head">
            <div class="book-list-title">Riwayat Usulan Pengadaan</div>
            <div class="member-badge"><i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> Menunggu persetujuan kepsek</div>
        </div>

        @if ($procurementSuggestions->count())
            @foreach ($procurementSuggestions as $procurement)
                <div class="book-row">
                    <div class="book-row-main">
                        <div class="book-cover-chip"><i data-lucide="clipboard-plus" class="w-5 h-5"></i></div>
                        <div class="book-row-meta">
                            <div class="book-row-title">{{ $procurement->title }}</div>
                            <div class="book-row-sub">{{ $procurement->author }} | Jumlah usulan {{ $procurement->quantity }}</div>
                            <div class="book-row-sub2">
                                Status {{ ucfirst($procurement->status) }}
                                | Pengusul {{ $procurement->proposer?->name ?? 'Petugas' }}
                                @if ($procurement->category?->name)
                                    | {{ $procurement->category->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="book-empty">
                <div class="text-lg font-semibold text-slate2-900">Belum ada usulan pengadaan</div>
                <div class="mt-2 text-sm">Usulan buku baru dari admin atau petugas akan muncul di sini sebelum disetujui kepsek.</div>
            </div>
        @endif
    </div>
</div>

<div id="bookDrawerMask" class="book-drawer-mask" onclick="closeBookDrawer()"></div>

<aside id="bookDrawer" class="book-drawer" aria-hidden="true">
    <div class="book-drawer-head">
        <div>
            <div class="book-drawer-title">Edit Buku</div>
            <div class="book-drawer-sub">Ubah detail bibliografi dan stok buku langsung dari panel samping tanpa pindah halaman.</div>
        </div>
        <button type="button" class="book-drawer-close" onclick="closeBookDrawer()">X</button>
    </div>

    <div class="book-drawer-body">
        <form id="editBookForm" method="POST" action="" enctype="multipart/form-data" class="space-y-3" data-async="true" data-success-call="closeBookDrawer" data-refresh-targets="#bookStatsWrap,#bookListWrap">
            @csrf
            @method('PUT')

            <div id="drawerBookPreviewWrap" style="display:none;">
                <img id="drawerBookPreview" src="" alt="Preview cover" style="width:96px;height:128px;border-radius:16px;object-fit:cover;border:1px solid var(--border);">
            </div>
            <input id="drawerBookTitle" name="title" class="form-input px-3 py-3 text-sm" placeholder="Judul buku" required>
            <div class="cover-upload js-cover-upload">
                <input type="file" name="cover_image" accept="image/*" class="js-file-upload">
                <input type="file" accept="image/*" capture="environment" class="js-camera-input">
                <input type="file" accept="image/*" class="js-gallery-input">
                <div class="upload-source-actions">
                    <button type="button" class="upload-source-btn js-open-camera">Foto Langsung</button>
                    <button type="button" class="upload-source-btn js-open-gallery">Pilih dari Galeri</button>
                </div>
                <div class="upload-selected-name file-upload-name is-empty">Belum ada file dipilih</div>
                <div class="upload-preview js-upload-preview">
                    <img alt="Preview cover baru">
                    <button type="button" class="upload-remove js-upload-remove">X</button>
                </div>
            </div>
            <div class="book-grid">
                <input id="drawerBookAuthor" name="author" class="form-input px-3 py-3 text-sm" placeholder="Nama penulis" required>
                <input id="drawerBookPublisher" name="publisher" class="form-input px-3 py-3 text-sm" placeholder="Penerbit">
            </div>
            <div class="book-grid">
                <input id="drawerBookIsbn" name="isbn" class="form-input px-3 py-3 text-sm" placeholder="ISBN">
                <input id="drawerBookPlace" name="place_of_publication" class="form-input px-3 py-3 text-sm" placeholder="Tempat terbit">
            </div>
            <div class="book-grid">
                <input id="drawerBookYear" name="published_year" type="number" min="1901" max="{{ now()->addYear()->format('Y') }}" class="form-input px-3 py-3 text-sm" placeholder="Tahun terbit">
                <select id="drawerBookCategory" name="category_id" class="form-select px-3 py-3 text-sm">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="book-category-box">
                <div class="book-category-title">Tambah Kategori Baru</div>
                <div class="space-y-4">
                    <input id="drawerInlineCategoryName" class="form-input px-3 py-3 text-sm" placeholder="Nama kategori baru">
                    <button id="drawerInlineCategoryButton" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold w-full" type="button">Tambah Kategori</button>
                </div>
            </div>
            <div class="book-grid-3">
                <input id="drawerBookStockTotal" type="number" name="stock_total" class="form-input px-3 py-3 text-sm" placeholder="Stok total" required>
                <input id="drawerBookStockAvailable" type="number" name="stock_available" class="form-input px-3 py-3 text-sm" placeholder="Stok tersedia" required>
                <div></div>
            </div>
            <textarea id="drawerBookDescription" name="description" class="form-textarea px-3 py-3 text-sm" rows="4" placeholder="Deskripsi buku"></textarea>

            <div class="book-actions-bottom">
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" type="submit">Simpan Perubahan</button>
                <button class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" type="button" onclick="closeBookDrawer()">Batal</button>
            </div>
        </form>
    </div>
</aside>

<div id="cropMask" class="crop-mask"></div>
<div id="cameraModal" class="camera-modal" aria-hidden="true">
    <div class="crop-head">
        <div>
            <div class="crop-title">Foto Langsung</div>
            <div class="text-sm text-slate2-600 mt-1">Kamera dibuka langsung di aplikasi, lalu hasilnya masuk ke crop cover.</div>
        </div>
        <button type="button" class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" onclick="closeCameraModal()">Tutup</button>
    </div>
    <div class="camera-preview">
        <video id="cameraVideo" autoplay playsinline muted></video>
    </div>
    <div class="camera-help">Perlu izin kamera dari browser. Fitur ini biasanya berjalan di `https` atau `localhost` pada HP.</div>
    <div class="camera-actions">
        <button type="button" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" onclick="closeCameraModal()">Batal</button>
        <button type="button" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" onclick="captureCameraPhoto()">Ambil Foto</button>
    </div>
</div>
<div id="cropModal" class="crop-modal" aria-hidden="true">
    <div class="crop-head">
        <div>
            <div class="crop-title">Atur Foto Cover</div>
            <div class="text-sm text-slate2-600 mt-1">Geser posisi dan zoom sebelum cover dipakai.</div>
        </div>
        <button type="button" class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" onclick="closeCropModal()">Tutup</button>
    </div>
    <div class="crop-stage-wrap">
        <div class="crop-stage">
            <div class="crop-frame" id="cropFrame">
                <img id="cropImage" alt="Crop image">
            </div>
        </div>
        <div class="crop-side">
            <div class="crop-hint">Drag foto langsung di area crop seperti aplikasi biasa untuk menentukan bagian yang dipakai.</div>
            <div>
                <div class="crop-label">Zoom</div>
                <input id="cropZoomRange" type="range" class="crop-range" min="1" max="3" step="0.01" value="1">
                <div id="cropZoomValue" class="crop-range-value">100%</div>
            </div>
            <div>
                <div class="crop-label">Preview</div>
                <div class="crop-preview">
                    <canvas id="cropCanvas" width="300" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="crop-actions">
        <button type="button" class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" onclick="closeCropModal()">Batal</button>
        <button type="button" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" onclick="applyCrop()">Pakai Foto Ini</button>
    </div>
</div>

<script>
    const bookDrawer = document.getElementById('bookDrawer');
    const bookDrawerMask = document.getElementById('bookDrawerMask');
    const editBookForm = document.getElementById('editBookForm');
    const drawerBookPreviewWrap = document.getElementById('drawerBookPreviewWrap');
    const drawerBookPreview = document.getElementById('drawerBookPreview');
    const cropMask = document.getElementById('cropMask');
    const cameraModal = document.getElementById('cameraModal');
    const cameraVideo = document.getElementById('cameraVideo');
    const cropModal = document.getElementById('cropModal');
    const cropFrame = document.getElementById('cropFrame');
    const cropImage = document.getElementById('cropImage');
    const cropCanvas = document.getElementById('cropCanvas');
    const cropZoomRange = document.getElementById('cropZoomRange');
    const cropZoomValue = document.getElementById('cropZoomValue');
    let activeSourceInput = null;
    let activeUploadInput = null;
    let activeUploadPreview = null;
    let activeUploadName = null;
    let cropSourceImage = null;
    let cropSourceUrl = '';
    let cropOffsetX = 0;
    let cropOffsetY = 0;
    let cropScale = 1;
    let cropDragging = false;
    let cropDragStartX = 0;
    let cropDragStartY = 0;
    let cropDragOriginX = 0;
    let cropDragOriginY = 0;
    let activeUploadRoot = null;
    let cameraStream = null;

    function openBookDrawer(button) {
        editBookForm.action = "{{ url('/admin/books') }}/" + button.dataset.id;
        document.getElementById('drawerBookTitle').value = button.dataset.title || '';
        document.getElementById('drawerBookAuthor').value = button.dataset.author || '';
        document.getElementById('drawerBookPublisher').value = button.dataset.publisher || '';
        document.getElementById('drawerBookIsbn').value = button.dataset.isbn || '';
        document.getElementById('drawerBookPlace').value = button.dataset.place || '';
        document.getElementById('drawerBookYear').value = button.dataset.publishedYear || '';
        document.getElementById('drawerBookStockTotal').value = button.dataset.stockTotal || '';
        document.getElementById('drawerBookStockAvailable').value = button.dataset.stockAvailable || '';
        document.getElementById('drawerBookCategory').value = button.dataset.categoryId || '';
        document.getElementById('drawerBookDescription').value = button.dataset.description || '';
        resetUploadState(editBookForm.querySelector('.js-cover-upload'));

        if (button.dataset.coverUrl) {
            drawerBookPreview.src = button.dataset.coverUrl;
            drawerBookPreviewWrap.style.display = 'block';
        } else {
            drawerBookPreview.src = '';
            drawerBookPreviewWrap.style.display = 'none';
        }

        bookDrawer.classList.add('open');
        bookDrawerMask.classList.add('show');
        bookDrawer.setAttribute('aria-hidden', 'false');
    }

    function closeBookDrawer() {
        bookDrawer.classList.remove('open');
        bookDrawerMask.classList.remove('show');
        bookDrawer.setAttribute('aria-hidden', 'true');
    }

    function resetUploadState(uploadRoot) {
        if (!uploadRoot) {
            return;
        }

        const preview = uploadRoot.querySelector('.js-upload-preview');
        const previewImg = preview.querySelector('img');
        const nameNode = uploadRoot.querySelector('.file-upload-name');
        const realInput = uploadRoot.querySelector('.js-file-upload');
        const cameraInput = uploadRoot.querySelector('.js-camera-input');
        const galleryInput = uploadRoot.querySelector('.js-gallery-input');

        realInput.value = '';
        cameraInput.value = '';
        galleryInput.value = '';
        preview.classList.remove('show');
        previewImg.removeAttribute('src');
        nameNode.textContent = 'Belum ada file dipilih';
        nameNode.classList.add('is-empty');
    }

    function openCropModal(sourceInput, uploadRoot, file) {
        activeSourceInput = sourceInput;
        activeUploadRoot = uploadRoot;
        activeUploadInput = uploadRoot.querySelector('.js-file-upload');
        activeUploadPreview = uploadRoot.querySelector('.js-upload-preview');
        activeUploadName = uploadRoot.querySelector('.file-upload-name');

        if (cropSourceUrl) {
            URL.revokeObjectURL(cropSourceUrl);
        }

        cropSourceUrl = URL.createObjectURL(file);
        cropSourceImage = new Image();
        cropSourceImage.onload = function () {
            cropImage.src = cropSourceUrl;
            cropOffsetX = 0;
            cropOffsetY = 0;
            cropScale = 1;
            cropZoomRange.value = '1';
            cropZoomValue.textContent = '100%';
            renderCrop();
            cropMask.classList.add('show');
            cropModal.classList.add('show');
            cropModal.setAttribute('aria-hidden', 'false');
        };
        cropSourceImage.src = cropSourceUrl;
    }

    async function openCameraModal(uploadRoot) {
        activeUploadRoot = uploadRoot;
        activeSourceInput = null;
        activeUploadInput = uploadRoot.querySelector('.js-file-upload');
        activeUploadPreview = uploadRoot.querySelector('.js-upload-preview');
        activeUploadName = uploadRoot.querySelector('.file-upload-name');

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' }
                },
                audio: false
            });

            cameraVideo.srcObject = cameraStream;
            cropMask.classList.add('show');
            cameraModal.classList.add('show');
            cameraModal.setAttribute('aria-hidden', 'false');
        } catch (error) {
            alert('Kamera tidak bisa dibuka. Pastikan izin kamera aktif dan aplikasi dibuka lewat HTTPS atau localhost.');
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
            cropMask.classList.remove('show');
        }
    }

    function closeCropModal(resetInput = true) {
        cropMask.classList.remove('show');
        cropModal.classList.remove('show');
        cropModal.setAttribute('aria-hidden', 'true');
        if (resetInput && activeSourceInput) {
            activeSourceInput.value = '';
        }
    }

    function renderCrop() {
        if (!cropSourceImage) {
            return;
        }

        const frameWidth = 300;
        const frameHeight = 400;
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

        const ctx = cropCanvas.getContext('2d');
        ctx.clearRect(0, 0, cropCanvas.width, cropCanvas.height);
        ctx.drawImage(cropSourceImage, left, top, drawWidth, drawHeight);
    }

    function applyCrop() {
        if (!cropSourceImage || !activeUploadInput || !activeUploadPreview) {
            return;
        }

        cropCanvas.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            const originalName = activeSourceInput && activeSourceInput.files && activeSourceInput.files[0]
                ? activeSourceInput.files[0].name
                : 'cover.png';
            const croppedFile = new File([blob], originalName, { type: 'image/png' });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            activeUploadInput.files = transfer.files;

            const previewUrl = URL.createObjectURL(blob);
            const previewImg = activeUploadPreview.querySelector('img');
            previewImg.src = previewUrl;
            activeUploadPreview.classList.add('show');
            activeUploadName.textContent = croppedFile.name;
            activeUploadName.classList.remove('is-empty');
            if (activeSourceInput) {
                activeSourceInput.value = '';
            }

            closeCropModal(false);
        }, 'image/png');
    }

    function captureCameraPhoto() {
        if (!cameraVideo.videoWidth || !cameraVideo.videoHeight || !activeUploadRoot) {
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

            const capturedFile = new File([blob], 'camera-cover.png', { type: 'image/png' });
            closeCameraModal();
            openCropModal(null, activeUploadRoot, capturedFile);
        }, 'image/png');
    }

    function clearSelectedImage(button) {
        resetUploadState(button.closest('.js-cover-upload'));
    }

    function resetCreateBookFormState() {
        const createBookForm = document.getElementById('createBookForm');
        if (!createBookForm) {
            return;
        }

        resetUploadState(createBookForm.querySelector('.js-cover-upload'));
    }

    function addBookCategoryOption(data) {
        const category = data && data.category ? data.category : null;
        if (!category || !category.id) {
            return;
        }

        ['createBookCategory', 'drawerBookCategory'].forEach(function (selectId) {
            const select = document.getElementById(selectId);
            if (!select) {
                return;
            }

            const existingOption = Array.from(select.options).find(function (option) {
                return option.value === String(category.id);
            });

            if (!existingOption) {
                const option = document.createElement('option');
                option.value = String(category.id);
                option.textContent = category.name;
                select.appendChild(option);
            }

            select.value = String(category.id);
        });
    }

    async function createInlineCategory(inputId) {
        const input = document.getElementById(inputId);
        if (!input) {
            return;
        }

        const name = input.value.trim();
        if (!name) {
            showAsyncToast('Nama kategori wajib diisi.', 'error');
            input.focus();
            return;
        }

        try {
            const response = await fetch("{{ route('admin.categories.store') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.content || "{{ csrf_token() }}"
                },
                body: new URLSearchParams({ name })
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'Kategori gagal ditambahkan.');
            }

            addBookCategoryOption(data);
            input.value = '';
            showAsyncToast(data.message || 'Kategori berhasil ditambahkan.', 'success');
        } catch (error) {
            showAsyncToast(error.message || 'Kategori gagal ditambahkan.', 'error');
        }
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-edit-book');
        if (!button) {
            return;
        }

        openBookDrawer(button);
    });

    document.querySelectorAll('.js-open-camera').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                button.closest('.js-cover-upload').querySelector('.js-camera-input').click();
                return;
            }

            openCameraModal(button.closest('.js-cover-upload'));
        });
    });

    document.querySelectorAll('.js-open-gallery').forEach(function (button) {
        button.addEventListener('click', function () {
            button.closest('.js-cover-upload').querySelector('.js-gallery-input').click();
        });
    });

    document.querySelectorAll('.js-camera-input, .js-gallery-input').forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.files && input.files.length) {
                openCropModal(input, input.closest('.js-cover-upload'), input.files[0]);
            }
        });
    });

    document.querySelectorAll('.js-upload-remove').forEach(function (button) {
        button.addEventListener('click', function () {
            clearSelectedImage(button);
        });
    });

    cropMask.addEventListener('click', function () {
        closeCameraModal();
        closeCropModal();
    });

    cropZoomRange.addEventListener('input', function () {
        cropScale = Number(cropZoomRange.value);
        cropZoomValue.textContent = Math.round(cropScale * 100) + '%';
        renderCrop();
    });

    cropFrame.addEventListener('pointerdown', function (event) {
        if (!cropSourceImage) {
            return;
        }

        cropDragging = true;
        cropDragStartX = event.clientX;
        cropDragStartY = event.clientY;
        cropDragOriginX = cropOffsetX;
        cropDragOriginY = cropOffsetY;
        cropFrame.classList.add('dragging');
        cropFrame.setPointerCapture(event.pointerId);
    });

    cropFrame.addEventListener('pointermove', function (event) {
        if (!cropDragging) {
            return;
        }

        cropOffsetX = cropDragOriginX + (event.clientX - cropDragStartX);
        cropOffsetY = cropDragOriginY + (event.clientY - cropDragStartY);
        renderCrop();
    });

    function stopCropDragging(event) {
        if (!cropDragging) {
            return;
        }

        cropDragging = false;
        cropFrame.classList.remove('dragging');
        if (event) {
            cropFrame.releasePointerCapture(event.pointerId);
        }
    }

    cropFrame.addEventListener('pointerup', stopCropDragging);
    cropFrame.addEventListener('pointercancel', stopCropDragging);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeBookDrawer();
            closeCameraModal();
            closeCropModal();
        }
    });

    document.getElementById('inlineCategoryButton')?.addEventListener('click', function () {
        createInlineCategory('inlineCategoryName');
    });

    document.getElementById('drawerInlineCategoryButton')?.addEventListener('click', function () {
        createInlineCategory('drawerInlineCategoryName');
    });
</script>
@endsection
