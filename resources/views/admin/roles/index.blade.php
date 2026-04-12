@extends('layouts.admin')

@section('content')
@php($title = 'Table Access')
@php($eyebrow = 'Role dan Permission')

<style>
    .access-page{display:flex;flex-direction:column;gap:24px}
    .access-shell{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(320px,.75fr);gap:20px}
    .access-card{background:var(--bg-card);border:1px solid var(--border);border-radius:22px;box-shadow:var(--shadow-sm)}
    .access-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:22px 24px 0}
    .access-card-body{padding:22px 24px 24px}
    .access-card-title{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
    .access-card-sub{font-size:14px;color:var(--muted);line-height:1.7;margin-top:8px}
    .access-table-wrap{overflow:auto;border-radius:18px;border:1px solid var(--border);background:#fff}
    .access-table{width:100%;border-collapse:separate;border-spacing:0;min-width:840px}
    .access-table th,.access-table td{padding:14px 16px;border-bottom:1px solid var(--border);vertical-align:middle}
    .access-table thead th{position:sticky;top:0;background:#fbf8f2;z-index:1;text-align:left;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
    .access-table tbody tr:last-child td{border-bottom:none}
    .access-table tbody tr:hover td{background:#fffdf9}
    .access-permission-title{font-size:14px;font-weight:700;color:var(--fg)}
    .access-permission-name{font-size:12px;color:var(--dim);margin-top:4px}
    .access-check{text-align:center}
    .access-check input{width:18px;height:18px;accent-color:var(--accent);cursor:pointer}
    .access-role-head{min-width:130px}
    .access-role-label{font-size:13px;font-weight:700;color:var(--fg)}
    .access-role-sub{font-size:11px;color:var(--dim);margin-top:4px}
    .access-actions{display:flex;justify-content:flex-end;margin-top:18px}
    .access-summary{display:flex;flex-direction:column;gap:14px}
    .access-summary-item{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 18px;border:1px solid var(--border);border-radius:16px;background:#fff}
    .access-summary-label{font-size:13px;font-weight:700;color:var(--fg)}
    .access-summary-sub{font-size:12px;color:var(--muted);margin-top:4px}
    .access-summary-value{font-size:22px;font-weight:800;color:var(--fg);line-height:1}
    .access-role-list{display:flex;flex-direction:column;gap:12px}
    .access-role-item{padding:16px 18px;border-radius:16px;border:1px solid var(--border);background:#fff}
    .access-role-item-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
    .access-role-item-title{font-size:14px;font-weight:700;color:var(--fg)}
    .access-role-item-sub{font-size:12px;color:var(--muted);margin-top:4px}
    .access-role-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--gold-light);color:var(--accent);font-size:11px;font-weight:700}
    .access-role-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px}
    .access-role-meta-box{padding:12px;border-radius:14px;background:#fbf8f2;border:1px solid var(--border)}
    .access-role-meta-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--dim)}
    .access-role-meta-value{font-size:18px;font-weight:800;color:var(--fg);margin-top:6px}
    @media (max-width:1180px){.access-shell{grid-template-columns:1fr}}
</style>

<div class="access-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Table Access</h1>
            <p class="member-subtitle">Checklist hak akses tiap role dalam satu tabel. Semua perubahan disimpan ke database pivot permission.</p>
        </div>
        <div class="member-badge"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i> {{ $roles->count() }} role aktif</div>
    </div>

    <section id="roleStats" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--accent);color:#fff;"><i data-lucide="shield-check" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $roles->count() }}</div><div class="member-mini-label">Total Role</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="key-round" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $permissions->count() }}</div><div class="member-mini-label">Permission Tersedia</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="users-round" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $roles->sum(fn($role) => $role->users->count()) }}</div><div class="member-mini-label">User Dalam Semua Role</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--orange-light);color:var(--orange);"><i data-lucide="list-checks" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $roles->sum(fn($role) => $role->permissions->count()) }}</div><div class="member-mini-label">Permission Terpasang</div></div>
        </div>
    </section>

    <div id="roleAccessShell" class="access-shell">
        <form method="POST" action="{{ route('admin.roles.matrix.update') }}" class="access-card" data-async="true" data-refresh-targets="#roleStats,#roleAccessShell">
            @csrf
            @method('PUT')

            <div class="access-card-head">
                <div>
                    <h2 class="access-card-title">Checklist Akses</h2>
                    <p class="access-card-sub">Centang permission yang boleh dilakukan role terkait. Misalnya admin bisa kelola user, sedangkan siswa hanya memakai fitur umum tanpa akses admin.</p>
                </div>
                <div class="access-role-pill"><i data-lucide="database" class="w-3.5 h-3.5"></i> Sinkron ke database</div>
            </div>

            <div class="access-card-body">
                <div class="access-table-wrap">
                    <table class="access-table">
                        <thead>
                            <tr>
                                <th>Permission</th>
                                @foreach ($roles as $role)
                                    <th class="access-role-head">
                                        <div class="access-role-label">{{ $role->label }}</div>
                                        <div class="access-role-sub">{{ $role->name }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="access-permission-title">{{ $permission->label }}</div>
                                        <div class="access-permission-name">{{ $permission->name }}</div>
                                    </td>
                                    @foreach ($roles as $role)
                                        <td class="access-check">
                                            <input
                                                type="checkbox"
                                                name="permissions[{{ $role->id }}][]"
                                                value="{{ $permission->id }}"
                                                @checked($role->permissions->contains($permission->id))
                                                aria-label="{{ $role->label }} - {{ $permission->label }}"
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="access-actions">
                    <button class="btn-primary rounded-xl px-5 py-3 text-sm font-semibold" type="submit">
                        <i data-lucide="save" class="w-4 h-4"></i>Simpan Table Access
                    </button>
                </div>
            </div>
        </form>

        <div class="access-summary">
            <div class="access-card">
                <div class="access-card-head">
                    <div>
                        <h2 class="access-card-title">Ringkasan</h2>
                        <p class="access-card-sub">Panel cepat untuk membaca kondisi role dan distribusi akses saat ini.</p>
                    </div>
                </div>
                <div class="access-card-body">
                    <div class="access-summary-item">
                        <div>
                            <div class="access-summary-label">Role dengan akses admin</div>
                            <div class="access-summary-sub">Role yang punya minimal satu permission panel admin.</div>
                        </div>
                        <div class="access-summary-value">{{ $roles->filter(fn($role) => $role->permissions->isNotEmpty())->count() }}</div>
                    </div>
                    <div class="access-summary-item">
                        <div>
                            <div class="access-summary-label">Role tanpa akses panel</div>
                            <div class="access-summary-sub">Cocok untuk user umum seperti siswa atau guru.</div>
                        </div>
                        <div class="access-summary-value">{{ $roles->filter(fn($role) => $role->permissions->isEmpty())->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="access-card">
                <div class="access-card-head">
                    <div>
                        <h2 class="access-card-title">Role Aktif</h2>
                        <p class="access-card-sub">Checklist di tabel kiri langsung mengikuti daftar role dari database.</p>
                    </div>
                </div>
                <div class="access-card-body">
                    <div class="access-role-list">
                        @foreach ($roles as $role)
                            <div class="access-role-item">
                                <div class="access-role-item-head">
                                    <div>
                                        <div class="access-role-item-title">{{ $role->label }}</div>
                                        <div class="access-role-item-sub">{{ $role->name }}</div>
                                    </div>
                                    <div class="access-role-pill">{{ $role->permissions->count() }} akses</div>
                                </div>
                                <div class="access-role-meta">
                                    <div class="access-role-meta-box">
                                        <div class="access-role-meta-label">Jumlah User</div>
                                        <div class="access-role-meta-value">{{ $role->users->count() }}</div>
                                    </div>
                                    <div class="access-role-meta-box">
                                        <div class="access-role-meta-label">Permission</div>
                                        <div class="access-role-meta-value">{{ $role->permissions->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
