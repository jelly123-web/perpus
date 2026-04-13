@extends('layouts.admin')

@section('content')
@php($title = 'Kelola Akun Pengguna')
@php($eyebrow = 'Khusus Superadmin')

<style>
    .account-shell{display:grid;grid-template-columns:minmax(320px,420px) minmax(0,1fr);gap:20px}
    .account-add{position:relative;overflow:hidden;border-radius:20px;padding:24px;background:var(--bg-card);border:1px solid var(--border);box-shadow:var(--shadow-sm)}
    .account-add:before{content:'';position:absolute;right:-40px;top:-40px;width:160px;height:160px;border-radius:999px;background:radial-gradient(circle,rgba(13,155,106,.10),transparent 70%)}
    .account-add > *{position:relative;z-index:1}
    .account-add-title{font-family:'Playfair Display',serif;font-size:34px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
    .account-add-sub{margin-top:8px;font-size:14px;line-height:1.7;color:var(--muted)}
    .account-badge{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:999px;background:var(--accent);color:#fff;font-size:11px;font-weight:700}
    .account-list{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:20px 20px 12px;box-shadow:var(--shadow-sm)}
    .account-list-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
    .account-list-title{font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:var(--fg)}
    .account-list-sub{font-size:14px;color:var(--muted);margin-top:4px}
    .account-item{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 6px;border-top:1px solid var(--border)}
    .account-item:first-child{border-top:none}
    .account-item-main{display:flex;align-items:center;gap:14px;min-width:0}
    .account-avatar{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,var(--accent),var(--accent-light));display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px;font-weight:700;flex-shrink:0}
    .account-meta{min-width:0}
    .account-name{font-size:15px;font-weight:700;color:var(--fg)}
    .account-sub{font-size:13px;color:var(--muted);margin-top:3px}
    .account-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .btn-danger-soft{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:var(--red);border:1px solid rgba(209,67,67,.18);border-radius:10px;padding:10px 14px;font-size:12px;font-weight:700;cursor:pointer;transition:.2s}
    .btn-danger-soft:hover{background:var(--red-light)}
    .account-empty{border:1px dashed var(--border-light);border-radius:18px;padding:40px 20px;text-align:center;color:var(--muted);background:var(--bg-soft)}
    .drawer-mask{position:fixed;inset:0;background:rgba(8,15,12,.28);opacity:0;pointer-events:none;transition:opacity .28s ease;z-index:70}
    .drawer-mask.show{opacity:1;pointer-events:auto}
    .account-drawer{position:fixed;top:0;right:0;width:min(520px,100vw);height:100vh;background:var(--bg-raised);box-shadow:-12px 0 40px rgba(0,0,0,.12);transform:translateX(100%);transition:transform .32s cubic-bezier(.4,0,.2,1);z-index:80;display:flex;flex-direction:column}
    .account-drawer.open{transform:translateX(0)}
    .drawer-head{padding:22px 22px 18px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .drawer-title{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--fg)}
    .drawer-sub{font-size:13px;color:var(--muted);margin-top:6px;line-height:1.6}
    .drawer-close{width:40px;height:40px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--muted);cursor:pointer}
    .drawer-body{padding:20px 22px 24px;overflow-y:auto}
    .drawer-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .drawer-note{margin-top:12px;padding:12px 14px;border-radius:12px;background:var(--gold-light);color:#7b5a07;font-size:12px;line-height:1.6}
    .drawer-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
    .pw-field{position:relative;display:flex;align-items:center}
    .pw-field .form-input{padding-right:44px!important;width:100%}
    .pw-toggle{position:absolute;right:14px;background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;display:flex;align-items:center;justify-content:center;transition:.2s}
    .pw-toggle:hover{color:var(--accent)}
    @media (max-width:1100px){.account-shell{grid-template-columns:1fr}}
    @media (max-width:640px){
        .drawer-grid{grid-template-columns:1fr}
        .account-item{flex-direction:column;align-items:flex-start}
        .account-actions{justify-content:flex-start}
    }
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Kelola Akun Pengguna</h1>
        </div>
        <div class="member-badge"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Akses superadmin</div>
    </div>

    <section id="usersStats" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--accent);color:#fff;"><i data-lucide="users" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $accountStats['total'] }}</div><div class="member-mini-label">Total Akun</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="briefcase-business" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $accountStats['petugas'] }}</div><div class="member-mini-label">Petugas dan Admin</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="graduation-cap" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $accountStats['peminjam'] }}</div><div class="member-mini-label">Peminjam Siswa dan Guru</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--red-light);color:var(--red);"><i data-lucide="user-check" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $accountStats['aktif'] }}</div><div class="member-mini-label">Akun Aktif</div></div>
        </div>
    </section>

    <div class="account-shell">
        <div class="account-add">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="account-add-title">Tambah Akun</div>
                </div>
                <div class="account-badge"><i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Akun Baru</div>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3 mt-5" data-async="true" data-reset-on-success="true" data-refresh-targets="#usersStats,#usersList">
                @csrf
                <div class="member-form-grid">
                    <input name="name" class="form-input px-3 py-3 text-sm" placeholder="Nama lengkap" required>
                    <input name="username" class="form-input px-3 py-3 text-sm" placeholder="Username login" required>
                </div>
                <div class="member-form-grid">
                    <input type="email" name="email" class="form-input px-3 py-3 text-sm" placeholder="Email" required>
                    <input name="phone" class="form-input px-3 py-3 text-sm" placeholder="No HP">
                </div>
                <div class="member-form-grid">
                    <input name="kelas" class="form-input px-3 py-3 text-sm" placeholder="Kelas, contoh XI IPA 2">
                    <input name="jurusan" class="form-input px-3 py-3 text-sm" placeholder="Jurusan, contoh IPA">
                </div>
                <select name="role_id" class="form-select px-3 py-3 text-sm" required>
                    <option value="">Pilih role akun</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->label }}</option>
                    @endforeach
                </select>
                <div class="pw-field">
                    <input type="password" name="password" class="form-input px-3 py-3 text-sm" placeholder="Password login" required>
                    <button type="button" class="pw-toggle js-pw-toggle"><i data-lucide="eye" class="w-4 h-4"></i></button>
                </div>
                <label class="member-check"><input type="checkbox" name="is_active" value="1" checked> Aktifkan akun setelah disimpan</label>
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Simpan Akun</button>
            </form>
        </div>

        <div id="usersList" class="account-list">
            <div class="account-list-head">
                <div>
                    <div class="account-list-title">Daftar Akun Pengguna</div>
                </div>
                <div class="account-badge"><i data-lucide="panel-right-open" class="w-3.5 h-3.5"></i> Edit via drawer</div>
            </div>

            @if ($users->count())
                @foreach ($users as $user)
                    <div class="account-item">
                        <div class="account-item-main">
                            <div class="account-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            <div class="account-meta">
                                <div class="account-name">{{ $user->name }}</div>
                                <div class="account-sub">{{ $user->role?->label ?? 'Tanpa role' }} | {{ $user->username }}</div>
                                @if ($user->academicLabel())
                                    <div class="account-sub">{{ $user->academicLabel() }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="account-actions">
                            <button
                                type="button"
                                class="btn-primary rounded-xl px-4 py-2 text-xs font-semibold js-edit-user"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-username="{{ $user->username }}"
                                data-email="{{ $user->email }}"
                                data-phone="{{ $user->phone }}"
                                data-kelas="{{ $user->kelas }}"
                                data-jurusan="{{ $user->jurusan }}"
                                data-role-id="{{ $user->role_id }}"
                                data-role-label="{{ $user->role?->label ?? 'Tanpa role' }}"
                                data-is-active="{{ $user->is_active ? '1' : '0' }}"
                            >Edit</button>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-async="true" data-confirm="Hapus akun ini?" data-remove-closest=".account-item" data-refresh-targets="#usersStats,#usersList">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger-soft" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4">{{ $users->links() }}</div>
            @else
                <div class="account-empty">
                    <div class="text-lg font-semibold text-slate2-900">Belum ada akun pengguna</div>
                    <div class="mt-2 text-sm">Akun baru akan muncul di sini setelah dibuat oleh super admin.</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div id="drawerMask" class="drawer-mask" onclick="closeUserDrawer()"></div>

<aside id="userDrawer" class="account-drawer" aria-hidden="true">
    <div class="drawer-head">
        <div>
            <div class="drawer-title">Edit Akun</div>
            <div class="drawer-sub">di eedit dulu akunnya :)</div>
        </div>
        <button type="button" class="drawer-close" onclick="closeUserDrawer()">X</button>
    </div>

    <div class="drawer-body">
        <form id="editUserForm" method="POST" action="" class="space-y-3" data-async="true" data-success-call="closeUserDrawer" data-refresh-targets="#usersStats,#usersList">
            @csrf
            @method('PUT')

            <div class="drawer-grid">
                <input id="drawerName" name="name" class="form-input px-3 py-3 text-sm" placeholder="Nama lengkap" required>
                <input id="drawerUsername" name="username" class="form-input px-3 py-3 text-sm" placeholder="Username login" required>
            </div>

            <div class="drawer-grid">
                <input id="drawerEmail" type="email" name="email" class="form-input px-3 py-3 text-sm" placeholder="Email" required>
                <input id="drawerPhone" name="phone" class="form-input px-3 py-3 text-sm" placeholder="No HP">
            </div>

            <div class="drawer-grid">
                <input id="drawerKelas" name="kelas" class="form-input px-3 py-3 text-sm" placeholder="Kelas, contoh XI IPA 2">
                <input id="drawerJurusan" name="jurusan" class="form-input px-3 py-3 text-sm" placeholder="Jurusan, contoh IPA">
            </div>

            <div class="drawer-grid">
                <select id="drawerRoleId" name="role_id" class="form-select px-3 py-3 text-sm" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->label }}</option>
                    @endforeach
                </select>
                <label class="member-check"><input id="drawerIsActive" type="checkbox" name="is_active" value="1"> Akun aktif</label>
            </div>

            <div class="pw-field">
                <input id="drawerPassword" type="password" name="password" class="form-input px-3 py-3 text-sm" placeholder="Masukkan password baru jika ingin diganti">
                <button type="button" class="pw-toggle js-pw-toggle"><i data-lucide="eye" class="w-4 h-4"></i></button>
            </div>

            <div class="drawer-note">
                Password lama tidak bisa ditampilkan kembali dari database. Jika ingin mengubah password, isi field di atas dengan password baru lalu simpan.
            </div>

            <div class="drawer-actions">
                <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold" type="submit">Simpan Perubahan</button>
                <button class="btn-soft rounded-xl px-4 py-3 text-sm font-semibold" type="button" onclick="closeUserDrawer()">Batal</button>
            </div>
        </form>
    </div>
</aside>

<script>
    const drawer = document.getElementById('userDrawer');
    const drawerMask = document.getElementById('drawerMask');
    const editUserForm = document.getElementById('editUserForm');
    const drawerName = document.getElementById('drawerName');
    const drawerUsername = document.getElementById('drawerUsername');
    const drawerEmail = document.getElementById('drawerEmail');
    const drawerPhone = document.getElementById('drawerPhone');
    const drawerKelas = document.getElementById('drawerKelas');
    const drawerJurusan = document.getElementById('drawerJurusan');
    const drawerRoleId = document.getElementById('drawerRoleId');
    const drawerIsActive = document.getElementById('drawerIsActive');
    const drawerPassword = document.getElementById('drawerPassword');

    function openUserDrawer(button) {
        editUserForm.action = "{{ url('/admin/users') }}/" + button.dataset.id;
        drawerName.value = button.dataset.name || '';
        drawerUsername.value = button.dataset.username || '';
        drawerEmail.value = button.dataset.email || '';
        drawerPhone.value = button.dataset.phone || '';
        drawerKelas.value = button.dataset.kelas || '';
        drawerJurusan.value = button.dataset.jurusan || '';
        drawerRoleId.value = button.dataset.roleId || '';
        drawerIsActive.checked = button.dataset.isActive === '1';
        drawerPassword.value = '';

        drawer.classList.add('open');
        drawerMask.classList.add('show');
        drawer.setAttribute('aria-hidden', 'false');
    }

    function closeUserDrawer() {
        drawer.classList.remove('open');
        drawerMask.classList.remove('show');
        drawer.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-edit-user');
        if (!button) {
            return;
        }

        openUserDrawer(button);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeUserDrawer();
        }
    });

    // Password visibility toggle
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.js-pw-toggle');
        if (!toggle) return;

        const input = toggle.parentElement.querySelector('input');
        const icon = toggle.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            if (icon && window.lucide) {
                toggle.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4"></i>';
                window.lucide.createIcons();
            }
        } else {
            input.type = 'password';
            if (icon && window.lucide) {
                toggle.innerHTML = '<i data-lucide="eye" class="w-4 h-4"></i>';
                window.lucide.createIcons();
            }
        }
    });
</script>
@endsection
