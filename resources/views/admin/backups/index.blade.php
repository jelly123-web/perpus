@extends('layouts.admin')

@section('content')
@php($title = 'Backup Data')
@php($eyebrow = 'Keamanan Sistem')

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Backup Data</h1>
            <p class="member-subtitle">Buat snapshot data perpustakaan lalu pantau riwayat file backup yang pernah dibuat dari panel super admin.</p>
        </div>
        <div class="member-badge"><i data-lucide="database-backup" class="w-3.5 h-3.5"></i> {{ $backups->total() }} backup tersimpan</div>
    </div>

    <section id="backupStats" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--accent);color:#fff;"><i data-lucide="database" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $backups->total() }}</div><div class="member-mini-label">Total Backup</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="hard-drive-download" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ number_format((int) $backups->sum('size_bytes') / 1024, 0) }}</div><div class="member-mini-label">Ukuran Total (KB)</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="clock-3" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $backups->first()?->created_at?->diffForHumans() ?? '-' }}</div><div class="member-mini-label">Backup Terakhir</div></div>
        </div>
    </section>

    <div class="grid xl:grid-cols-3 gap-5">
        <div class="member-add-card">
            <div class="member-card-head">
                <div>
                    <h3 class="member-card-title">Buat Backup Baru</h3>
                    <p class="member-card-sub">Sistem akan menyimpan snapshot tabel utama ke file JSON di `storage/app/backups`.</p>
                </div>
                <div class="member-badge"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Snapshot</div>
            </div>

            <form method="POST" action="{{ route('admin.backups.store') }}" class="space-y-3" data-async="true" data-refresh-targets="#backupStats,#backupList">
                @csrf
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full" type="submit">
                    <i data-lucide="database-backup" class="w-4 h-4"></i>Buat Backup Sekarang
                </button>
            </form>
        </div>

        <div id="backupList" class="xl:col-span-2 crd member-list-card">
            <h3 class="font-serif text-lg font-bold text-slate2-900">Riwayat Backup</h3>
            <div class="space-y-4 mt-4">
                @forelse ($backups as $backup)
                    <div class="member-item border border-slate2-100 rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div>
                                <div class="text-sm font-semibold text-slate2-900">{{ $backup->file_name }}</div>
                                <div class="text-xs text-slate2-400 mt-1">{{ $backup->file_path }}</div>
                                <div class="text-xs text-slate2-400 mt-2">
                                    Dibuat {{ $backup->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                    | Oleh {{ $backup->creator?->name ?? 'Sistem' }}
                                </div>
                            </div>
                            <div class="member-badge">
                                {{ number_format(((int) $backup->size_bytes) / 1024, 1) }} KB
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate2-100 p-6 text-sm text-slate2-400">
                        Belum ada backup. Tekan tombol di kiri untuk membuat snapshot pertama.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $backups->links() }}</div>
        </div>
    </div>
</div>
@endsection
