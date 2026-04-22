@extends('layouts.admin')

@section('content')
@php($title = 'Restore Data')
@php($eyebrow = 'Khusus Superadmin')

<style>
    .restore-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;border-bottom:1px solid var(--border-light);padding-bottom:24px;margin-bottom:28px}
    .restore-title{font-size:36px;font-weight:800;color:var(--fg)}
    .restore-subtitle{font-size:15px;color:var(--muted);margin-top:8px;max-width:680px;line-height:1.6}
    .restore-badge{background:var(--accent);color:#fff;padding:10px 16px;border-radius:999px;font-size:12px;font-weight:800;display:flex;align-items:center;gap:8px}
    .restore-group{background:linear-gradient(180deg,#fffdf9 0%,#fff7ef 100%);border:1px solid rgba(196,149,106,.36);border-radius:20px;box-shadow:0 14px 32px rgba(196,149,106,.14);margin-bottom:18px;overflow:hidden}
    .restore-group-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;background:linear-gradient(180deg,rgba(196,149,106,.34),rgba(196,149,106,.18));border-bottom:1px solid rgba(196,149,106,.3)}
    .restore-group-title{font-size:16px;font-weight:800;color:var(--fg)}
    .restore-count{font-size:12px;font-weight:800;color:#9b6a3d;background:rgba(255,248,240,.96);border:1px solid rgba(196,149,106,.34);border-radius:999px;padding:7px 12px}
    .restore-row{display:grid;grid-template-columns:minmax(180px,1.2fr) minmax(150px,.8fr) minmax(130px,.7fr) minmax(110px,.6fr) minmax(120px,.7fr) minmax(110px,.6fr) auto;gap:16px;align-items:center;padding:18px 20px;border-top:1px solid rgba(196,149,106,.2);background:linear-gradient(180deg,#fff9f3 0%,#fffefc 100%)}
    .restore-row:first-child{border-top:none}
    .restore-name{font-size:14px;font-weight:800;color:var(--fg)}
    .restore-meta-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--dim);margin-bottom:4px}
    .restore-meta-value{font-size:13px;color:var(--muted)}
    .restore-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .restore-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--accent);background:var(--accent);color:#fff;border-radius:12px;padding:10px 14px;font-size:12px;font-weight:800;cursor:pointer;transition:.2s}
    .restore-btn:hover{background:var(--accent-light);border-color:var(--accent-light);transform:translateY(-1px)}
    .restore-btn-danger{border-color:rgba(196,69,54,.18);background:var(--red-light);color:var(--red)}
    .restore-btn-danger:hover{background:#f9dfdb;border-color:var(--red);color:var(--red);transform:translateY(-1px)}
    .restore-empty{background:#fff;border:1px solid var(--border-light);border-radius:20px;padding:48px 24px;text-align:center;color:var(--muted);box-shadow:var(--shadow-sm)}
    .restore-empty i{width:44px;height:44px;margin:0 auto 14px;opacity:.35}
    @media(max-width:900px){.restore-row{grid-template-columns:1fr}.restore-actions,.restore-btn{width:100%}}
</style>

<div class="member-page">
    <div class="restore-head">
        <div>
            <h1 class="font-display restore-title">Restore Data</h1>
            <p class="restore-subtitle">Data yang dihapus masuk ke daftar ini. Super admin bisa melihat siapa yang menghapus, IP perangkat, hari, tanggal, dan jam hapus sebelum mengembalikannya.</p>
        </div>
        <div class="restore-badge"><i data-lucide="archive-restore" class="w-4 h-4"></i> {{ $deletedTotal }} Data Terhapus</div>
    </div>

    <div id="restoreList">
        @forelse($groups as $group)
            <section class="restore-group">
                <div class="restore-group-head">
                    <div class="restore-group-title">{{ $group['label'] }}</div>
                    <div class="restore-count">{{ $group['count'] }} data</div>
                </div>
                <div>
                    @foreach($group['items'] as $item)
                        <div class="restore-row">
                            <div>
                                <div class="restore-meta-label">{{ $item['label'] }}</div>
                                <div class="restore-name">{{ $item['name'] }}</div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Dihapus oleh</div>
                                <div class="restore-meta-value">{{ $item['deleted_by'] }}</div>
                            </div>
                            <div>
                                <div class="restore-meta-label">IP</div>
                                <div class="restore-meta-value">{{ $item['deleted_ip'] }}</div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Hari</div>
                                <div class="restore-meta-value">{{ $item['deleted_day'] }}</div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Tanggal</div>
                                <div class="restore-meta-value">{{ $item['deleted_date'] }}</div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Jam</div>
                                <div class="restore-meta-value">{{ $item['deleted_time'] }}</div>
                            </div>
                            <div class="restore-actions">
                                <form method="POST" action="{{ route('admin.restore.restore', [$item['table'], $item['id']]) }}" data-async="true" data-confirm="Kembalikan data ini?" data-remove-closest=".restore-row">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="restore-btn">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Restore
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.restore.force-delete', [$item['table'], $item['id']]) }}" data-async="true" data-confirm="Hapus permanen data ini? Tindakan ini tidak bisa dibatalkan." data-remove-closest=".restore-row">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="restore-btn restore-btn-danger">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Permanen
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="restore-empty">
                <i data-lucide="archive-restore"></i>
                <p>Tidak ada data yang sedang terhapus.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
