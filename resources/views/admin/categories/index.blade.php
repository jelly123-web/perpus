@extends('layouts.admin')

@section('content')
@php($title = 'Kategori Buku')
@php($eyebrow = 'Manajemen Label')

<style>
    .cat-shell{display:grid;grid-template-columns:minmax(320px,430px) minmax(0,1fr);gap:32px}
    .cat-add{position:relative;overflow:hidden;border-radius:24px;padding:32px;background:var(--bg-card);border:1px solid var(--border-light);box-shadow:var(--shadow-sm)}
    .cat-add:before{content:'';position:absolute;right:-50px;top:-50px;width:170px;height:170px;border-radius:999px;background:radial-gradient(circle,rgba(var(--accent-rgb),.12),transparent 70%)}
    .cat-add > *{position:relative;z-index:1}
    .cat-add-title{font-family:'Playfair Display',serif;font-size:32px;font-weight:800;letter-spacing:-.03em;color:var(--fg)}
    .cat-card{background:var(--bg-card);border:1px solid var(--border-light);border-radius:24px;padding:32px 32px 18px;box-shadow:var(--shadow-sm)}
    .cat-list-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:24px}
    .cat-list-title{font-family:'Playfair Display',serif;font-size:24px;font-weight:800;color:var(--fg)}
    .cat-row{padding:24px;border-radius:20px;background:var(--bg-soft);border:1px solid var(--border-light);transition:.3s cubic-bezier(.4,0,.2,1)}
    .cat-row:hover{background:#fff;box-shadow:var(--shadow-md);transform:translateY(-2px)}
    .cat-row-title{font-size:16px;font-weight:800;color:var(--fg)}
    .cat-row-desc{font-size:14px;color:var(--muted);margin-top:6px;line-height:1.6}
    .cat-actions{display:flex;align-items:center;gap:10px;margin-top:18px;padding-top:18px;border-top:1px solid var(--border-light)}
    
    .btn-cat-glow{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:var(--fg);border:1px solid var(--border-light);border-radius:12px;padding:10px 16px;font-size:12px;font-weight:700;cursor:pointer;transition:.3s cubic-bezier(.4,0,.2,1);box-shadow:var(--shadow-sm)}
    .btn-cat-glow:hover{background:var(--bg-soft);color:var(--accent);border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 24px var(--accent-glow)}
    .btn-cat-glow:focus,
    .btn-cat-glow:focus-visible,
    .btn-cat-glow:active{outline:none}
    .btn-cat-glow.primary{background:var(--accent);color:#fff;border-color:var(--accent)}
    .btn-cat-glow.primary:hover,
    .btn-cat-glow.primary:focus,
    .btn-cat-glow.primary:focus-visible,
    .btn-cat-glow.primary:active{background:var(--accent-light);color:#fff;border-color:var(--accent-light);box-shadow:0 0 0 3px rgba(196,149,106,.18)}
    .btn-cat-glow.danger{background:var(--red-light);color:var(--red);border-color:rgba(196,69,54,.16)}
    .btn-cat-glow.danger:hover,
    .btn-cat-glow.danger:focus,
    .btn-cat-glow.danger:focus-visible,
    .btn-cat-glow.danger:active{background:var(--red-light);color:var(--red);border-color:var(--red);box-shadow:0 0 0 3px rgba(196,69,54,.14)}

    .member-toolbar{border-bottom: 1px solid var(--border-light); padding-bottom: 24px; margin-bottom: 32px;}
    .member-title{font-size: 36px; font-weight: 800;}
    .member-subtitle{font-size: 16px; color: var(--muted); margin-top: 8px;}

    @media (max-width:1100px){.cat-shell{grid-template-columns:1fr}}
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Kategori Buku</h1>
            <p class="member-subtitle">Atur kelompok buku berdasarkan topik, jenis koleksi, atau rak untuk memudahkan pencarian katalog.</p>
        </div>
        <div class="member-badge" style="background:var(--accent);color:#fff;padding:8px 16px;border-radius:999px;font-size:12px;font-weight:800;display:flex;align-items:center;gap:8px;"><i data-lucide="tags" class="w-4 h-4"></i> {{ $categories->total() }} Kategori</div>
    </div>

    <section id="categoryStats" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--accent);color:#fff;"><i data-lucide="tags" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->total() }}</div><div class="member-mini-label">Total Kategori</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="book" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->sum('books_count') }}</div><div class="member-mini-label">Buku Tercatat</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="check-circle" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->where('books_count', '>', 0)->count() }}</div><div class="member-mini-label">Kategori Aktif</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--red-light);color:var(--red);"><i data-lucide="alert-circle" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->where('books_count', 0)->count() }}</div><div class="member-mini-label">Kategori Kosong</div></div>
        </div>
    </section>

    <div class="cat-shell">
        <div class="cat-add">
            <h2 class="cat-add-title">Tambah Kategori</h2>
            <p class="text-sm text-slate-500 mt-2 mb-8">Buat label kategori baru untuk mengelompokkan koleksi buku.</p>
            
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5" data-async="true" data-reset-on-success="true" data-refresh-targets="#categoryStats,#categoryList">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kategori</label>
                    <input name="name" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Contoh: Novel, Sains, Sejarah..." required>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Deskripsi (Opsional)</label>
                    <textarea name="description" class="form-textarea w-full px-4 py-3.5 text-sm rounded-xl" rows="4" placeholder="Penjelasan singkat kategori..."></textarea>
                </div>
                <button type="submit" class="btn-cat-glow primary w-full py-4 rounded-xl font-bold mt-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Simpan Kategori
                </button>
            </form>
        </div>

        <div id="categoryList" class="cat-card">
            <div class="cat-list-head">
                <h2 class="cat-list-title">Daftar Kategori</h2>
                <div class="text-xs font-bold text-slate-400 bg-slate-50 px-4 py-2 rounded-full border border-slate-100">
                    {{ $categories->total() }} Total
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                @forelse($categories as $category)
                    <div class="cat-row">
                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-4" data-async="true" data-refresh-targets="#categoryStats,#categoryList">
                            @csrf
                            @method('PUT')
                            <div class="space-y-1.5">
                                <input name="name" value="{{ $category->name }}" class="form-input w-full px-3 py-2.5 text-sm rounded-xl font-bold bg-white" placeholder="Nama kategori">
                            </div>
                            <div class="space-y-1.5">
                                <textarea name="description" class="form-textarea w-full px-3 py-2.5 text-sm rounded-xl bg-white" rows="2" placeholder="Deskripsi">{{ $category->description }}</textarea>
                            </div>
                            <div class="cat-actions">
                                <span class="text-[11px] font-bold text-slate-400 bg-white px-3 py-1 rounded-full border border-slate-100 mr-auto">
                                    {{ $category->books_count }} buku
                                </span>
                                <button type="submit" class="btn-cat-glow primary" style="padding: 8px 14px;">Update</button>
                        </form>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-async="true" data-confirm="Hapus kategori ini?" data-remove-closest=".cat-row" data-refresh-targets="#categoryStats,#categoryList">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-cat-glow danger" style="padding: 8px 14px;">Hapus</button>
                                </form>
                            </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400">
                        <i data-lucide="tags" class="w-12 h-12 mx-auto mb-4 opacity-20"></i>
                        <p>Belum ada data kategori.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
