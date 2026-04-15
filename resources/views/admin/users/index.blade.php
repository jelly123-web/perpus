@extends('layouts.admin')

@section('content')
@php($title = 'Kelola Akun Pengguna')
@php($eyebrow = 'Khusus Superadmin')

<style>
    .account-shell{display:grid;grid-template-columns:minmax(320px,430px) minmax(0,1fr);gap:32px}
    .account-add{position:relative;overflow:hidden;border-radius:24px;padding:32px;background:var(--bg-card);border:1px solid var(--border-light);box-shadow:var(--shadow-sm)}
    .account-add:before{content:'';position:absolute;right:-50px;top:-50px;width:170px;height:170px;border-radius:999px;background:radial-gradient(circle,rgba(196,149,106,.16),transparent 70%)}
    .account-add > *{position:relative;z-index:1}
    .account-add-title{font-family:Georgia,'Times New Roman',Times,serif;font-size:32px;font-weight:800;letter-spacing:-.03em;color:var(--fg)}
    .account-card{background:var(--bg-card);border:1px solid var(--border-light);border-radius:24px;padding:32px 32px 18px;box-shadow:var(--shadow-sm)}
    .account-list-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:24px}
    .account-list-title{font-family:Georgia,'Times New Roman',Times,serif;font-size:24px;font-weight:800;color:var(--fg)}
    .account-total-pill{font-size:12px;font-weight:800;color:var(--muted);background:var(--bg-soft);padding:10px 14px;border-radius:999px;border:1px solid var(--border-light)}
    .account-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 6px;border-top:1px solid var(--border-light)}
    .account-row:hover{background:rgba(255,253,249,.8)}
    .account-row:first-child{border-top:none}
    .account-row-main{display:flex;align-items:center;gap:18px;min-width:0}
    .account-avatar-chip{width:56px;height:56px;border-radius:16px;background:var(--accent-glow);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;flex-shrink:0;box-shadow:var(--shadow-sm)}
    .account-row-meta{min-width:0}
    .account-row-title{font-size:16px;font-weight:800;color:var(--fg)}
    .account-row-sub{font-size:14px;color:var(--muted);margin-top:3px}
    .account-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    
    .btn-account-glow{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:var(--fg);border:1px solid var(--border-light);border-radius:12px;padding:12px 16px;font-size:13px;font-weight:700;cursor:pointer;transition:.3s cubic-bezier(.4,0,.2,1);box-shadow:var(--shadow-sm)}
    .btn-account-glow:hover{background:var(--bg-soft);color:var(--accent);border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 24px var(--accent-glow)}
    .btn-account-glow.primary{background:var(--accent);color:#fff;border-color:var(--accent)}
    .btn-account-glow.primary:hover{background:var(--accent-light);box-shadow:0 8px 24px var(--accent-glow)}
    .btn-account-glow.danger:hover{color:var(--red);border-color:var(--red);box-shadow:0 8px 24px rgba(196,69,54,.14)}
    .account-empty{padding:56px 20px;text-align:center;color:var(--dim)}
    .account-empty i{width:48px;height:48px;margin:0 auto 14px;opacity:.3}

    .report-usage-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px}
    .report-usage-widget{background:#fff;border:1px solid var(--border-light);border-radius:22px;padding:22px;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;gap:10px;min-height:128px;transition:.2s ease}
    .report-usage-widget:hover{background:var(--bg-soft);box-shadow:var(--shadow-md)}
    .report-usage-tag{font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.12em}
    .report-usage-number{font-size:28px;font-weight:800;color:var(--fg);line-height:1}
    .report-usage-desc{font-size:13px;color:var(--dim);font-weight:500}

    .member-toolbar{border-bottom: 1px solid var(--border-light); padding-bottom: 24px; margin-bottom: 32px;}
    .member-title{font-size: 36px; font-weight: 800;}
    .member-subtitle{font-size: 16px; color: var(--muted); margin-top: 8px;}

    .drawer-mask{position:fixed;inset:0;background:rgba(8,15,12,.28);opacity:0;pointer-events:none;transition:opacity .28s ease;z-index:70}
    .drawer-mask.show{opacity:1;pointer-events:auto}
    .account-drawer{position:fixed;top:0;right:0;width:min(560px,100vw);height:100vh;background:var(--bg-raised);box-shadow:-12px 0 40px rgba(0,0,0,.12);transform:translateX(100%);transition:transform .32s cubic-bezier(.4,0,.2,1);z-index:80;display:flex;flex-direction:column}
    .account-drawer.open{transform:translateX(0)}
    .drawer-head{padding:32px;border-bottom:1px solid var(--border-light);display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .drawer-title{font-family:Georgia,'Times New Roman',Times,serif;font-size:28px;font-weight:800;color:var(--fg)}
    .drawer-close{width:44px;height:44px;border-radius:14px;border:1px solid var(--border-light);background:#fff;color:var(--muted);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s}
    .drawer-close:hover{background:var(--bg-soft);color:var(--accent);border-color:var(--accent)}
    .drawer-body{padding:32px;overflow-y:auto}

    @media (max-width:1100px){.account-shell{grid-template-columns:1fr}}
    @media (max-width:768px){
        .account-add,.account-card{padding:24px}
        .member-title{font-size:30px}
        .account-row{flex-direction:column;align-items:flex-start;padding-inline:0}
        .account-actions{justify-content:flex-start}
        .report-usage-row{grid-template-columns:1fr 1fr}
    }
    @media (max-width:560px){
        .report-usage-row,.grid.grid-cols-2{grid-template-columns:1fr}
        .btn-account-glow{width:100%}
        .account-actions{width:100%}
    }
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Kelola Akun Pengguna</h1>
            <p class="member-subtitle">Manajemen akses sistem: tambah petugas baru, daftarkan anggota, dan atur status aktif akun.</p>
        </div>
        <div class="member-badge" style="background:var(--accent);color:#fff;padding:8px 16px;border-radius:999px;font-size:12px;font-weight:800;display:flex;align-items:center;gap:8px;"><i data-lucide="shield-check" class="w-4 h-4"></i> Akses superadmin</div>
    </div>

    <section id="usersStats" class="report-usage-row" style="margin-bottom: 32px;">
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--accent);">Total Akun</div>
            <div class="report-usage-number">{{ $accountStats['total'] }}</div>
            <div class="report-usage-desc">Pengguna terdaftar</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--gold);">Staf & Admin</div>
            <div class="report-usage-number">{{ $accountStats['petugas'] }}</div>
            <div class="report-usage-desc">Petugas aktif</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--teal);">Peminjam</div>
            <div class="report-usage-number">{{ $accountStats['peminjam'] }}</div>
            <div class="report-usage-desc">Siswa & Guru</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--red);">Akun Aktif</div>
            <div class="report-usage-number">{{ $accountStats['aktif'] }}</div>
            <div class="report-usage-desc">Status login aktif</div>
        </div>
    </section>

    <div class="account-shell">
        <div class="account-add">
            <h2 class="account-add-title">Tambah Akun</h2>
            <p class="text-sm text-slate-500 mt-2 mb-8">Daftarkan petugas atau anggota perpustakaan baru.</p>
            
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5" data-async="true" data-reset-on-success="true" data-refresh-targets="#usersStats,#accountList">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                    <input name="name" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Nama lengkap..." required>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email</label>
                    <input type="email" name="email" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Email aktif..." required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Username</label>
                        <input name="username" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Username..." required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Password</label>
                        <input type="password" name="password" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peran (Role)</label>
                    <select name="role_id" class="form-select w-full px-4 py-3.5 text-sm rounded-xl" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-account-glow primary w-full py-4 rounded-xl font-bold mt-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Simpan Pengguna
                </button>
            </form>
        </div>

        <div id="accountList" class="account-card">
            <div class="account-list-head">
                <h2 class="account-list-title">Daftar Pengguna</h2>
                <div class="account-total-pill">
                    {{ $users->total() }} Total
                </div>
            </div>

            <div class="flex flex-col">
                @forelse($users as $user)
                    <div class="account-row">
                        <div class="account-row-main">
                            <div class="account-avatar-chip">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="account-row-meta">
                                <div class="account-row-title">{{ $user->name }}</div>
                                <div class="account-row-sub">@<span>{{ $user->username }}</span> • {{ $user->role?->name }}</div>
                            </div>
                        </div>
                        <div class="account-actions">
                            <button onclick="openEditDrawer({{ $user->id }})" class="btn-account-glow">
                                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                            </button>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-async="true" data-confirm="Hapus akun ini?" data-remove-closest=".account-row" data-refresh-targets="#usersStats">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-account-glow danger">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="account-empty">
                        <i data-lucide="users"></i>
                        <p>Belum ada data pengguna.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<div id="drawerMask" class="drawer-mask" onclick="closeEditDrawer()"></div>
<div id="editDrawer" class="account-drawer">
    <div class="drawer-head">
        <div>
            <h2 class="drawer-title">Edit Akun</h2>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi profil atau ganti password.</p>
        </div>
        <button onclick="closeEditDrawer()" class="drawer-close">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    <div id="drawerBody" class="drawer-body">
        <!-- Content will be loaded via AJAX -->
        <div class="flex items-center justify-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-800"></div>
        </div>
    </div>
</div>

<script>
    function openEditDrawer(userId) {
        const drawer = document.getElementById('editDrawer');
        const mask = document.getElementById('drawerMask');
        const body = document.getElementById('drawerBody');
        
        mask.classList.add('show');
        drawer.classList.add('open');
        
        fetch(`/admin/users/${userId}/edit`)
            .then(res => res.text())
            .then(html => {
                body.innerHTML = html;
                lucide.createIcons();
            });
    }

    function closeEditDrawer() {
        document.getElementById('editDrawer').classList.remove('open');
        document.getElementById('drawerMask').classList.remove('show');
    }
</script>
@endsection
