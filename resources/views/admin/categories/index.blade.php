@extends('layouts.admin')

@section('content')
@php($title = 'Kategori Buku')
@php($eyebrow = 'Kelola Kategori')
<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Kategori Buku</h1>
            <p class="member-subtitle">Gunakan pola halaman user untuk mengatur kelompok buku agar katalog lebih rapi.</p>
        </div>
        <div class="member-badge"><i data-lucide="tags" class="w-3.5 h-3.5"></i> {{ $categories->total() }} kategori tersimpan</div>
    </div>

    <section id="categoryStats" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--accent);color:#fff;"><i data-lucide="tags" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->total() }}</div><div class="member-mini-label">Total Kategori</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="library-big" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->sum('books_count') }}</div><div class="member-mini-label">Buku Tercatat</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="folder-open" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->where('books_count', '>', 0)->count() }}</div><div class="member-mini-label">Kategori Berisi Buku</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--red-light);color:var(--red);"><i data-lucide="folder-x" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $categories->where('books_count', 0)->count() }}</div><div class="member-mini-label">Kategori Kosong</div></div>
        </div>
    </section>

    <div class="grid xl:grid-cols-3 gap-5">
        <div class="member-add-card">
            <div class="member-card-head">
                <div>
                    <h3 class="member-card-title">Tambah Kategori</h3>
                    <p class="member-card-sub">Tambahkan kategori baru untuk mengelompokkan buku berdasarkan topik atau jenis koleksi.</p>
                </div>
                <div class="member-badge"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Label Baru</div>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-3" data-async="true" data-reset-on-success="true" data-refresh-targets="#categoryStats,#categoryList">@csrf
                <input name="name" class="form-input w-full px-3 py-3 text-sm" placeholder="Nama kategori" required>
                <textarea name="description" class="form-textarea w-full px-3 py-3 text-sm" rows="4" placeholder="Deskripsi"></textarea>
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Simpan Kategori</button>
            </form>
        </div>
        <div id="categoryList" class="xl:col-span-2 crd member-list-card">
            <h3 class="font-serif text-lg font-bold text-slate2-900">Daftar Kategori</h3>
            <div class="space-y-4 mt-4">
                @foreach($categories as $category)
                    <div class="member-item border border-slate2-100 rounded-2xl p-4">
                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-3" data-async="true" data-refresh-targets="#categoryStats,#categoryList">@csrf @method('PUT')
                            <input name="name" value="{{ $category->name }}" class="form-input w-full px-3 py-2 text-sm">
                            <textarea name="description" class="form-textarea w-full px-3 py-2 text-sm" rows="3">{{ $category->description }}</textarea>
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <p class="text-xs text-slate2-400">{{ $category->books_count }} buku</p>
                                <div class="flex gap-2">
                                    <button class="btn-primary rounded-xl px-4 py-2 text-xs font-semibold">Update</button>
                        </form>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-async="true" data-confirm="Hapus kategori ini?" data-remove-closest=".member-item" data-refresh-targets="#categoryStats,#categoryList">@csrf @method('DELETE')
                                        <button class="btn-soft rounded-xl px-4 py-2 text-xs font-semibold">Hapus</button>
                                    </form>
                                </div>
                            </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $categories->links() }}</div>
        </div>
    </div>
</div>
@endsection
