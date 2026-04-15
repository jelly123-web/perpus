<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $user = auth()->user();
        $appName = \App\Models\Setting::valueOr('app_name', 'LibraVault');
        $appLogo = \App\Models\Setting::appLogoPath();
        $appColor = \App\Models\Setting::valueOr('app_color', '#FAFAFA');
        $isPetugasPanel = $user?->role?->name === 'petugas';
        $headerNotifications = collect();
        $sidebarSections = collect([
            [
                'title' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'dashboard', 'match' => 'dashboard', 'permission' => 'access_dashboard'],
                    ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'permission' => 'view_reports'],
                    ['label' => 'Peminjaman Buku', 'icon' => 'book-up-2', 'route' => 'admin.loans.index', 'match' => 'admin.loans.*', 'permission' => 'manage_loans'],
                    ['label' => 'Riwayat Peminjaman', 'icon' => 'history', 'route' => 'borrower.history', 'match' => 'borrower.history', 'permission' => 'view_borrower_history'],
                ],
            ],
            [
                'title' => 'Manajemen Sistem',
                'items' => [
                    ['label' => 'Kelola Akun Pengguna', 'icon' => 'users-round', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'permission' => 'manage_users'],
                    ['label' => 'Table Access', 'icon' => 'shield-check', 'route' => 'admin.roles.index', 'match' => 'admin.roles.*', 'permission' => 'manage_roles'],
                    ['label' => 'Kategori Buku', 'icon' => 'tags', 'route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'permission' => 'manage_categories'],
                    ['label' => 'Kelola Data Buku', 'icon' => 'book-copy', 'route' => 'admin.books.index', 'match' => 'admin.books.index', 'permission' => 'manage_books'],
                    ['label' => 'Scan Barcode', 'icon' => 'scan-line', 'route' => 'admin.books.scan', 'match' => 'admin.books.scan', 'permission' => 'scan_books'],
                    ['label' => 'Backup Data', 'icon' => 'database-backup', 'route' => 'admin.backups.index', 'match' => 'admin.backups.*', 'permission' => 'manage_backups'],
                    ['label' => 'Setting', 'icon' => 'settings-2', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'permission' => 'manage_settings'],
                ],
            ],
        ])->map(function (array $section) use ($user): array {
            $items = collect($section['items'])
                ->filter(fn (array $item) => $user?->hasPermission($item['permission']))
                ->values();

            return [
                'title' => $section['title'],
                'items' => $items,
            ];
        })->filter(fn (array $section) => $section['items']->isNotEmpty())->values();

        if ($user?->hasPermission('manage_loans')) {
            $headerNotifications = $headerNotifications
                ->merge(
                    \App\Models\Loan::query()
                        ->with(['book', 'member'])
                        ->where('status', 'late')
                        ->latest('due_at')
                        ->take(3)
                        ->get()
                        ->map(fn ($loan) => [
                            'icon' => 'triangle-alert',
                            'tone' => 'danger',
                            'title' => 'Peminjaman terlambat',
                            'body' => ($loan->member?->name ?? 'Member').' belum mengembalikan '.($loan->book?->title ?? 'buku'),
                            'time' => optional($loan->due_at)->diffForHumans(),
                            'timestamp' => optional($loan->due_at)->timestamp ?? 0,
                            'href' => route('admin.loans.index'),
                        ])
                )
                ->merge(
                    \App\Models\Loan::query()
                        ->with(['book', 'member'])
                        ->latest()
                        ->take(6)
                        ->get()
                        ->map(fn ($loan) => [
                            'icon' => $loan->status === 'returned' ? 'badge-check' : 'book-up-2',
                            'tone' => $loan->status === 'returned' ? 'success' : 'info',
                            'title' => $loan->status === 'returned' ? 'Buku dikembalikan' : 'Peminjaman baru',
                            'body' => ($loan->member?->name ?? 'Member').' '.($loan->status === 'returned' ? 'mengembalikan' : 'meminjam').' '.($loan->book?->title ?? 'buku'),
                            'time' => optional($loan->created_at)->diffForHumans(),
                            'timestamp' => optional($loan->created_at)->timestamp ?? 0,
                            'href' => route('admin.loans.index'),
                        ])
                );
        }

        $headerNotifications = $headerNotifications
            ->sortByDesc('timestamp')
            ->take(6)
            ->values();
        $headerNotificationCount = $headerNotifications->count();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} - {{ $appName }}</title>
    @if ($appLogo)
        <link rel="icon" type="image/png" href="{{ asset($appLogo) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{
            --bg:#f8f6f1;--bg-raised:#fffdf9;--bg-card:#ffffff;--bg-soft:#f8f6f1;--fg:#1a2e35;--muted:#5a6d73;--dim:#8b989d;
            --accent:#c4956a;--accent-light:#d4a574;--accent-glow:rgba(196,149,106,.14);--gold:#c4956a;--gold-light:#f5ede5;
            --red:#c44536;--red-light:#fdf0ee;--teal:#2d8659;--teal-light:rgba(45,134,89,.10);--purple:#7c4ddb;--purple-light:#f0ebfa;
            --orange:#d4a03a;--orange-light:rgba(212,160,58,.12);--border:#e2ddd4;--border-light:#d7d0c4;--shadow-sm:0 1px 3px rgba(87,59,33,.05);
            --shadow-md:0 12px 32px rgba(87,59,33,.10);--shadow-lg:0 18px 42px rgba(87,59,33,.14);
            --primary:#0f4c5c;--primary-dark:#0a3642
        }
        *{box-sizing:border-box} html,body{margin:0;padding:0} body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:var(--bg);color:var(--fg);min-height:100vh;overflow-x:hidden}
        .font-display{font-family:Georgia,'Times New Roman',Times,serif}
        .admin-shell{display:flex;min-height:100vh;position:relative}
        .admin-shell:before{content:'';position:fixed;inset:0;background-image:radial-gradient(ellipse at 20% 20%, rgba(15,76,92,.03) 0%, transparent 50%),radial-gradient(ellipse at 80% 80%, rgba(196,149,106,.05) 0%, transparent 50%),url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230f4c5c' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");pointer-events:none;z-index:0}
        .side-mask{display:none;position:fixed;inset:0;background:rgba(0,0,0,.22);z-index:35}
        .sidebar{width:280px;background:var(--bg-raised);border-right:1px solid var(--border);position:fixed;left:0;top:0;bottom:0;z-index:40;display:flex;flex-direction:column;box-shadow:var(--shadow-sm);transition:transform .35s cubic-bezier(.4,0,.2,1);transform:translateX(-100%);padding:32px 20px}
        .sidebar.open{transform:translateX(0)}
        .side-mask.show{display:block}
        .sidebar-logo{padding:0 12px 32px;border-bottom:1px solid var(--border-light);margin-bottom:24px}
        .sidebar-nav{flex:1;padding:0;overflow-y:auto}
        .nav-section{font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:var(--dim);padding:24px 12px 12px;font-weight:800}
        .nav-link{display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:16px;color:var(--muted);font-size:14px;font-weight:600;text-decoration:none;transition:.3s cubic-bezier(.4,0,.2,1);position:relative;margin-bottom:6px}
        .nav-link:hover{background:var(--bg-soft);color:var(--accent);transform:translateX(4px)}
        .nav-link.active{background:var(--accent);color:#fff;font-weight:700;box-shadow:0 8px 20px var(--accent-glow)}
        .nav-link.active:before{display:none}
        .nav-link .nav-icon{width:20px;height:20px}
        .nav-badge{margin-left:auto;background:var(--red);color:#fff;border-radius:999px;padding:2px 7px;font-size:10px;font-weight:700}
        .sidebar-foot{padding:16px 12px 0;margin-top:auto}
        .sidebar-upgrade{background:linear-gradient(135deg, var(--bg-card), var(--bg-soft));border:1px solid var(--border-light);border-radius:20px;padding:20px;box-shadow:var(--shadow-sm);position:relative;overflow:hidden}
        .sidebar-upgrade:after{content:'';position:absolute;right:-20px;bottom:-20px;width:60px;height:60px;border-radius:999px;background:var(--accent-glow);z-index:0}
        .sidebar-upgrade > *{position:relative;z-index:1}
        .sidebar-focus-title{font-size:13px;font-weight:800;color:var(--fg);margin-bottom:8px;display:flex;align-items:center;gap:8px}
        .sidebar-focus-sub{font-size:12px;color:var(--muted);line-height:1.6;margin:0}
        .sidebar-focus-link{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:16px;padding:12px 16px;background:var(--accent);color:#fff;border-radius:12px;font-size:12px;font-weight:700;text-decoration:none;transition:.2s ease}
        .sidebar-focus-link:hover{background:var(--accent-dark);transform:translateY(-2px);box-shadow:0 4px 12px var(--accent-glow)}
        .main-area{margin-left:0;flex:1;min-height:100vh;position:relative;z-index:1}
        @media (min-width:1024px){
            .sidebar{z-index:40;border-right:1px solid var(--border-light);background:var(--bg-raised)}
            .sidebar.open + .main-area{margin-left:280px}
            .side-mask{display:none!important}
            .sidebar.open + .main-area .topbar-brand{display:none}
        }
        .topbar{position:sticky;top:0;z-index:30;background:var(--bg);border-bottom:1px solid var(--border);box-shadow:var(--shadow-sm)}
        .topbar-inner{height:64px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;gap:16px}
        .topbar-left,.topbar-right{display:flex;align-items:center;gap:12px}
        .hamburger{display:flex;width:40px;height:40px;border:1px solid var(--border-light);border-radius:10px;background:#fff;color:var(--fg);align-items:center;justify-content:center;cursor:pointer;font-size:24px;font-weight:700;line-height:1;letter-spacing:.04em}
        .hamburger:hover{color:var(--accent);border-color:var(--accent);background:var(--bg-soft)}
        .topbar-brand{display:flex;align-items:center;gap:12px}
        .topbar-brand-mark,.sidebar-brand-mark{display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--brand-logo-bg, linear-gradient(135deg, var(--accent), var(--accent-light)));border:1px solid rgba(255,255,255,.92);box-shadow:0 14px 30px rgba(196,149,106,.2)}
        .topbar-brand-mark{width:44px;height:44px;border-radius:14px}
        .sidebar-brand-mark{width:44px;height:44px;border-radius:12px}
        .topbar-brand-mark img,.sidebar-brand-mark img{width:100%;height:100%;object-fit:contain;padding:8px}
        .topbar-brand-text{display:flex;flex-direction:column;justify-content:center}
        .topbar-brand-title{font-size:18px;font-weight:700;letter-spacing:-.03em;color:var(--fg);line-height:1.1}
        .topbar-brand-sub{font-size:11px;color:var(--muted);line-height:1.1;margin-top:3px}
        .topbar-btn,.user-chip{border:1px solid var(--border-light);background:#fff;box-shadow:var(--shadow-sm)}
        .topbar-btn{width:44px;height:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:var(--fg)}
        .topbar-btn:hover{color:var(--accent);background:var(--bg-soft)}
        .notif-wrap{position:relative}
        .notif-btn{position:relative}
        .notif-badge{position:absolute;top:-6px;right:-4px;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:var(--accent);color:#fff;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px var(--accent-glow);border:2px solid var(--bg)}
        .notif-panel{position:absolute;top:calc(100% + 12px);right:0;width:min(380px,calc(100vw - 32px));background:#fff;border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow-lg);overflow:hidden;opacity:0;pointer-events:none;transform:translateY(8px);transition:.2s ease;z-index:60}
        .notif-panel.show{opacity:1;pointer-events:auto;transform:translateY(0)}
        .notif-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 18px 14px;border-bottom:1px solid var(--border);background:#fffdf9}
        .notif-head-title{font-size:14px;font-weight:800;color:var(--fg)}
        .notif-head-sub{font-size:12px;color:var(--muted);margin-top:4px}
        .notif-list{max-height:420px;overflow:auto}
        .notif-item{display:flex;align-items:flex-start;gap:12px;padding:14px 18px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;background:#fff}
        .notif-item:last-child{border-bottom:none}
        .notif-item:hover{background:#fffdf9}
        .notif-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .notif-icon.info{background:rgba(15,76,92,.08);color:var(--primary)}
        .notif-icon.success{background:rgba(45,134,89,.1);color:var(--success)}
        .notif-icon.danger{background:rgba(196,69,54,.1);color:var(--danger)}
        .notif-body{min-width:0;flex:1}
        .notif-title{font-size:13px;font-weight:700;color:var(--fg)}
        .notif-text{font-size:12px;color:var(--muted);line-height:1.55;margin-top:4px}
        .notif-time{font-size:11px;color:var(--dim);margin-top:7px}
        .notif-empty{padding:24px 18px;text-align:center;color:var(--muted);font-size:13px}
        .user-chip{display:flex;align-items:center;gap:10px;border-radius:12px;padding:6px 10px}
        .avatar{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,var(--accent-light),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700}
        .page-wrap{padding:28px 24px}
        .crd{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow-sm);transition:.25s}
        .crd:hover{border-color:var(--border-light);box-shadow:var(--shadow-md)}
        .tag-input,.form-input,.form-select,.form-textarea{width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:12px;background:var(--bg-card);color:var(--fg);font-family:inherit;transition:.2s;font-size:14px}
        .tag-input:focus,.form-input:focus,.form-select:focus,.form-textarea:focus{outline:none;border-color:var(--primary);background:#fff;box-shadow:0 0 0 3px rgba(15,76,92,0.1)}
        .form-select{appearance:none}
        .btn-primary,.btn-soft{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:none;border-radius:12px;cursor:pointer;transition:.2s;font-family:inherit;font-weight:700;padding:12px 16px;font-size:13px}
        .btn-primary{background:var(--accent);color:#fff;box-shadow:0 6px 16px rgba(196,149,106,.28)}
        .btn-primary:hover{background:var(--accent-light);transform:translateY(-1px);box-shadow:0 8px 20px rgba(196,149,106,.35)}
        .btn-soft{background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 6px 16px rgba(196,149,106,.28)}
        .btn-soft:hover{background:var(--accent-light);color:#fff;border-color:var(--accent-light);transform:translateY(-1px);box-shadow:0 8px 20px rgba(196,149,106,.35)}
        .btn-logout{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 18px;border-radius:12px;background:#fff;border:1px solid var(--border-light);color:var(--fg);font-size:13px;font-weight:700;cursor:pointer;transition:.25s cubic-bezier(.4,0,.2,1);box-shadow:var(--shadow-sm);text-decoration:none}
        .btn-logout:hover{background:var(--bg-soft);color:var(--accent);border-color:var(--accent);transform:translateY(-1px);box-shadow:0 8px 24px var(--accent-glow)}
        .btn-logout:active{transform:translateY(0);box-shadow:0 4px 10px var(--accent-glow)}
        .alert-box{border-radius:14px;border:1px solid var(--border);padding:14px 16px;font-size:14px}
        .alert-success{background:rgba(45,134,89,.1);color:var(--teal);border-color:rgba(45,134,89,.12)}
        .alert-error{background:var(--red-light);color:var(--red);border-color:rgba(196,69,54,.12)}
        .async-toast{position:fixed;right:20px;bottom:20px;z-index:200;min-width:260px;max-width:min(420px,calc(100vw - 24px));padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:#fff;box-shadow:var(--shadow-lg);opacity:0;transform:translateY(12px);pointer-events:none;transition:.24s ease}
        .async-toast.show{opacity:1;transform:translateY(0)}
        .async-toast.success{border-color:rgba(45,134,89,.18);background:rgba(45,134,89,.08);color:var(--teal)}
        .async-toast.error{border-color:rgba(196,69,54,.18);background:var(--red-light);color:var(--red)}
        .page-wrap input[type="checkbox"]{accent-color:var(--accent)}
        .page-wrap .border,.page-wrap [class*="border-slate2-100"]{border-color:var(--border)!important}
        .page-wrap .rounded-2xl{border-radius:16px}
        .page-wrap .text-slate2-900,.page-wrap .text-slate2-800{color:var(--fg)!important}
        .page-wrap .text-slate2-600{color:var(--muted)!important}
        .page-wrap .text-slate2-400{color:var(--dim)!important}
        .page-wrap .font-serif{font-family:Georgia,'Times New Roman',Times,serif}
        .page-wrap .text-sage-700{color:var(--accent)!important}
        .page-wrap .bg-sage-50{background:rgba(15,76,92,.08)!important}
        .page-wrap .bg-red-50{background:var(--red-light)!important}
        .page-wrap .border-red-100,.page-wrap .border-sage-100{border-color:var(--border)!important}
        .page-wrap .space-y-4 > .border,.page-wrap .space-y-4 > form.border{background:#fff;box-shadow:var(--shadow-sm)}
        .page-wrap .space-y-4 > .border:hover,.page-wrap .space-y-4 > form.border:hover{box-shadow:var(--shadow-md)}
        .page-wrap nav[role="navigation"]{margin-top:16px}
        .page-wrap nav[role="navigation"] > div{gap:10px}
        .page-wrap nav[role="navigation"] a,.page-wrap nav[role="navigation"] span{border-radius:10px}
        ::-webkit-scrollbar{width:6px;height:6px}::-webkit-scrollbar-thumb{background:var(--border-light);border-radius:999px}
        @media(max-width:1023px){
            .topbar-inner{padding:0 16px}.page-wrap{padding:20px 16px}
        }
        @media(max-width:640px){
            .user-chip div:last-child{display:none}
        }
        @if (!request()->routeIs('dashboard'))
        .member-page{display:flex;flex-direction:column;gap:24px}
        .member-toolbar{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .member-title{font-size:26px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
        .member-subtitle{font-size:14px;color:var(--muted);margin-top:4px}
        .member-mini-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
        .member-mini-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:var(--shadow-sm);transition:.25s}
        .member-mini-stat:hover{box-shadow:var(--shadow-md);transform:translateY(-1px)}
        .member-mini-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
        .member-mini-value{font-size:22px;font-weight:700;line-height:1}
        .member-mini-label{font-size:12px;color:var(--muted);margin-top:2px}
        .member-add-card{position:relative;overflow:hidden;border-radius:18px;padding:22px;background:var(--bg-card);border:1px solid var(--border);box-shadow:var(--shadow-sm)}
        .member-add-card:before{content:'';position:absolute;right:-40px;top:-50px;width:180px;height:180px;border-radius:999px;background:radial-gradient(circle,rgba(13,155,106,.10),transparent 70%)}
        .member-add-card:after{content:'';position:absolute;left:-60px;bottom:-90px;width:180px;height:180px;border-radius:999px;background:radial-gradient(circle,rgba(184,134,11,.08),transparent 70%)}
        .member-add-card > *{position:relative;z-index:1}
        .member-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px}
        .member-card-title{font-family:Georgia,'Times New Roman',Times,serif;font-size:24px;font-weight:700;color:var(--fg);letter-spacing:-.03em}
        .member-card-sub{font-size:13px;color:var(--muted);margin-top:4px;line-height:1.6}
        .member-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--accent);color:#fff;font-size:11px;font-weight:700}
        .member-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .member-check{display:flex;align-items:center;gap:10px;padding:11px 14px;border:1px solid var(--border);background:var(--bg);border-radius:10px;color:var(--muted);font-size:13px}
        .member-list-card{padding:20px}
        .member-item{border:1px solid var(--border)!important;background:var(--bg-card);box-shadow:var(--shadow-sm);transition:.25s}
        .member-item:hover{box-shadow:var(--shadow-md);border-color:var(--border-light)!important}
        @media (max-width:1024px){.member-mini-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width:768px){.member-form-grid,.member-mini-stats{grid-template-columns:1fr}.member-card-head{flex-direction:column}.member-title{font-size:24px}}
        @endif

        /* Dashboard Styles - Globalized */
        .dbx{position:relative;min-height:100%;padding:4px 0 8px}
        .dbx-pattern{position:absolute;inset:0;border-radius:24px;background-image:
            radial-gradient(ellipse at 20% 20%, rgba(15,76,92,.03) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 80%, rgba(196,149,106,.05) 0%, transparent 50%),
            url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230f4c5c' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events:none}
        .dbx-body{position:relative;z-index:1}
        .dbx-welcome{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;padding:4px 2px}
        .dbx-welcome-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:rgba(196,149,106,.12);color:#9a7530;font-size:11px;font-weight:700}
        .dbx-welcome-title{font-family:Georgia,'Times New Roman',Times,serif;font-size:30px;font-weight:700;letter-spacing:-.03em;color:#1a2e35;margin-top:12px}
        .dbx-welcome-sub{font-size:14px;color:#5a6d73;line-height:1.7;margin-top:8px}
        .dbx-book-grid{display:grid;grid-auto-flow:column;grid-auto-columns:120px;gap:16px;justify-content:start}
        .dbx-borrower-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-bottom:24px}
        .dbx-borrower-stat{background:#fff;border:1px solid #e2ddd4;border-radius:18px;padding:18px 20px;box-shadow:0 8px 24px rgba(15,76,92,.04)}
        .dbx-borrower-stat-value{font-size:28px;font-weight:700;color:#1a2e35}
        .dbx-borrower-stat-label{font-size:13px;color:#5a6d73;margin-top:4px}
        .dbx-borrower-alert{margin-bottom:20px;padding:16px 18px;border-radius:18px;border:1px solid rgba(196,69,54,.18);background:rgba(253,240,238,.9);color:#9f2d20}
        .dbx-borrower-profile{display:grid;grid-template-columns:1fr;gap:18px;margin-bottom:24px}
        .dbx-borrower-panel{background:#fff;border:1px solid #e2ddd4;border-radius:18px;padding:18px 20px;box-shadow:0 8px 24px rgba(15,76,92,.04)}
        .dbx-borrower-panel-title{font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7b6d60}
        .dbx-borrower-panel-value{font-size:22px;font-weight:700;color:#1a2e35;margin-top:8px}
        .dbx-borrower-panel-sub{font-size:13px;color:#5a6d73;line-height:1.7;margin-top:8px}
        .dbx-notif-list{display:grid;gap:12px;margin-bottom:24px}
        .dbx-notif-item{padding:16px 18px;border-radius:18px;border:1px solid #e2ddd4;background:#fff;box-shadow:0 8px 24px rgba(15,76,92,.04)}
        .dbx-notif-item.info{border-color:rgba(15,76,92,.15);background:rgba(15,76,92,.04)}
        .dbx-notif-item.success{border-color:rgba(45,134,89,.18);background:rgba(45,134,89,.06)}
        .dbx-notif-item.danger{border-color:rgba(196,69,54,.18);background:rgba(253,240,238,.9)}
        .dbx-notif-title{font-size:14px;font-weight:700;color:#1a2e35}
        .dbx-notif-body{font-size:12px;color:#5a6d73;line-height:1.7;margin-top:6px}
        .dbx-toast-wrap{position:fixed;right:22px;bottom:22px;display:grid;gap:10px;z-index:120}
        .dbx-toast{min-width:min(320px,calc(100vw - 32px));max-width:360px;padding:14px 16px;border-radius:16px;border:1px solid #e2ddd4;background:#fff;box-shadow:0 18px 38px rgba(15,76,92,.16);transform:translateY(18px);opacity:0;transition:.28s ease}
        .dbx-toast.show{transform:translateY(0);opacity:1}
        .dbx-toast.info{border-color:rgba(15,76,92,.18)}
        .dbx-toast.success{border-color:rgba(45,134,89,.18)}
        .dbx-toast.danger{border-color:rgba(196,69,54,.18)}
        .dbx-toast-title{font-size:13px;font-weight:700;color:#1a2e35}
        .dbx-toast-body{font-size:12px;color:#5a6d73;line-height:1.6;margin-top:4px}
        .dbx-book-filters{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(180px,.8fr) minmax(180px,.8fr) auto;gap:12px;margin-bottom:22px}
        .dbx-book-filter-field{width:100%;padding:12px 14px;border:1px solid #e2ddd4;border-radius:12px;background:#fff;font-size:14px;color:#1a2e35}
        .dbx-book-filter-field:focus{outline:none;border-color:#0f4c5c;box-shadow:0 0 0 3px rgba(15,76,92,.08)}
        .dbx-book-filter-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:none;border-radius:12px;background:#c4956a;color:#fff;font-size:13px;font-weight:700;cursor:pointer}
        .dbx-book-filter-btn:hover{background:#b78558}
        .dbx-book-showcase{position:relative;padding:10px 0 2px}
        .dbx-book-grid-wrap{overflow-x:auto;padding-bottom:8px}
        .dbx-book-grid-wrap::-webkit-scrollbar{height:8px}
        .dbx-book-grid-wrap::-webkit-scrollbar-thumb{background:rgba(15,76,92,.18);border-radius:999px}
        .dbx-book-card{display:flex;flex-direction:column;align-items:flex-start;background:transparent;border:none;border-radius:20px;padding:0;box-shadow:none;transition:.2s;cursor:pointer}
        .dbx-book-card:hover{transform:translateY(-3px)}
        .dbx-book-thumb{width:100%;aspect-ratio:3/4.35;border-radius:16px;background:linear-gradient(160deg,#f4eee3,#fffdf9);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;box-shadow:0 14px 30px rgba(15,76,92,.10);border:1px solid rgba(226,221,212,.9)}
        .dbx-book-thumb img{width:100%;height:100%;object-fit:cover}
        .dbx-book-fallback{width:58px;height:82px;border-radius:12px;background:linear-gradient(135deg,#c4956a,#d4a574);display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;font-weight:800}
        .dbx-book-body{min-width:0;flex:1;padding:10px 2px 0}
        .dbx-book-chip{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:rgba(45,134,89,.1);color:#2d8659;font-size:10px;font-weight:700}
        .dbx-book-chip.unavailable{background:rgba(196,69,54,.1);color:#c44536}
        .dbx-book-name{font-size:14px;font-weight:700;color:#1a2e35;line-height:1.38;margin-top:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:38px}
        .dbx-book-author{font-size:11px;color:#7a8a8f;margin-top:4px;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden}
        .dbx-book-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:8px;font-size:11px;color:#7a8a8f}
        .dbx-book-stock{font-weight:700;color:#0f4c5c}
        .dbx-book-form{margin-top:16px;display:flex;flex-direction:column;gap:10px}
        .dbx-book-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .dbx-book-note{width:100%;padding:11px 12px;border:1px solid #e2ddd4;border-radius:12px;background:#fff;font-size:13px;color:#1a2e35;min-height:78px}
        .dbx-book-note:focus{outline:none;border-color:#0f4c5c;box-shadow:0 0 0 3px rgba(15,76,92,.08)}
        .dbx-book-submit{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 14px;border:none;border-radius:12px;background:#c4956a;color:#fff;font-size:13px;font-weight:700;cursor:pointer}
        .dbx-book-submit:hover:not([disabled]){background:#b78558}
        .dbx-book-submit[disabled]{opacity:.55;cursor:not-allowed}
        .dbx-book-open{display:inline-flex;align-items:center;gap:6px;margin-top:8px;font-size:11px;font-weight:700;color:#0f4c5c}
        .dbx-book-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:14px}
        .dbx-book-section-copy{max-width:540px}
        .dbx-book-section-sub{font-size:12px;color:#7a8a8f;line-height:1.6;margin-top:6px}
        .dbx-book-section-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#f8f6f1;color:#7b6d60;font-size:11px;font-weight:700;border:1px solid #eee3d8;white-space:nowrap}
        .dbx-drawer-mask{position:fixed;inset:0;background:rgba(8,15,12,.34);opacity:0;pointer-events:none;transition:opacity .28s ease;z-index:70}
        .dbx-drawer-mask.show{opacity:1;pointer-events:auto}
        .dbx-drawer{position:fixed;top:0;right:0;width:min(460px,100vw);height:100vh;background:#fff;box-shadow:-16px 0 40px rgba(15,76,92,.14);transform:translateX(100%);transition:transform .32s cubic-bezier(.4,0,.2,1);z-index:80;display:flex;flex-direction:column}
        .dbx-drawer.open{transform:translateX(0)}
        .dbx-drawer-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:22px 22px 18px;border-bottom:1px solid #e2ddd4}
        .dbx-drawer-title{font-family:Georgia,'Times New Roman',Times,serif;font-size:28px;font-weight:700;color:#1a2e35;line-height:1.1}
        .dbx-drawer-sub{font-size:13px;color:#5a6d73;margin-top:8px;line-height:1.6}
        .dbx-drawer-close{width:40px;height:40px;border-radius:12px;border:1px solid #c4956a;background:#c4956a;color:#fff;cursor:pointer;flex-shrink:0}
        .dbx-drawer-close:hover{background:#b78558;border-color:#b78558}
        .dbx-drawer-body{padding:20px 22px 24px;overflow-y:auto}
        .dbx-drawer-book{display:flex;gap:14px;align-items:flex-start;padding:14px;border-radius:18px;background:#f8f6f1;border:1px solid #eee3d8;margin-bottom:20px}
        .dbx-drawer-thumb{width:78px;height:108px;border-radius:16px;background:linear-gradient(135deg,#f1e8d8,#fffdf9);overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .dbx-drawer-thumb img{width:100%;height:100%;object-fit:cover}
        .dbx-drawer-fallback{width:44px;height:64px;border-radius:12px;background:linear-gradient(135deg,#c4956a,#d4a574);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;font-weight:800}
        .dbx-drawer-book-title{font-size:18px;font-weight:700;color:#1a2e35;line-height:1.4}
        .dbx-drawer-book-author{font-size:13px;color:#5a6d73;margin-top:6px}
        .dbx-drawer-book-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}
        .dbx-drawer-book-box{padding:12px;border-radius:14px;background:#fff;border:1px solid #e2ddd4}
        .dbx-drawer-book-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7b6d60}
        .dbx-drawer-book-value{font-size:13px;color:#1a2e35;margin-top:6px;line-height:1.5}
        .dbx-drawer-help{margin:16px 0 0;font-size:12px;color:#5a6d73;line-height:1.6}
        .dbx-empty-drawer{padding:36px 18px;border:1px dashed #e2ddd4;border-radius:18px;text-align:center;color:#5a6d73;background:#fcfbf8}
        .dbx-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px}
        .dbx-stat{background:#fff;border-radius:16px;padding:24px;border:1px solid #e2ddd4;position:relative;overflow:hidden;transition:.3s}
        .dbx-stat:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(15,76,92,.1)}
        .dbx-stat:before{content:'';position:absolute;top:0;right:0;width:100px;height:100px;border-radius:50%;opacity:.1;transform:translate(30%,-30%)}
        .dbx-stat.books:before{background:#0f4c5c}.dbx-stat.members:before{background:#c4956a}.dbx-stat.borrowed:before{background:#2d8659}.dbx-stat.overdue:before{background:#c44536}
        .dbx-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px}
        .dbx-stat.books .dbx-stat-icon{background:rgba(15,76,92,.1);color:#0f4c5c}
        .dbx-stat.members .dbx-stat-icon{background:rgba(196,149,106,.15);color:#c4956a}
        .dbx-stat.borrowed .dbx-stat-icon{background:rgba(45,134,89,.1);color:#2d8659}
        .dbx-stat.overdue .dbx-stat-icon{background:rgba(196,69,54,.1);color:#c44536}
        .dbx-stat-value{font-size:32px;font-weight:700;color:#1a2e35;letter-spacing:-1px;margin-bottom:4px}
        .dbx-stat-label{font-size:14px;color:#5a6d73;margin-bottom:12px}
        .dbx-stat-trend{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600}
        .dbx-stat-trend.up{color:#2d8659}.dbx-stat-trend.down{color:#c44536}
        .dbx-content{display:grid;grid-template-columns:2fr 1fr;gap:24px}
        .dbx-card{background:#fff;border-radius:16px;border:1px solid #e2ddd4;overflow:hidden;transition:.3s}
        .dbx-card:hover{box-shadow:0 12px 32px rgba(15,76,92,.06)}
        .dbx-card-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #e2ddd4;gap:14px}
        .dbx-card-title{font-size:16px;font-weight:600;color:#1a2e35}
        .dbx-card-action{font-size:13px;color:#0f4c5c;font-weight:500;text-decoration:none}
        .dbx-card-body{padding:24px}
        .dbx-table-wrap{overflow-x:auto}
        .dbx-table{width:100%;border-collapse:collapse}
        .dbx-table th{text-align:left;padding:12px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:#5a6d73;border-bottom:1px solid #e2ddd4}
        .dbx-table td{padding:16px;border-bottom:1px solid #e2ddd4;font-size:14px}
        .dbx-table tr:last-child td{border-bottom:none}
        .dbx-table tbody tr:hover{background:rgba(15,76,92,.02)}
        .dbx-book-info,.dbx-member-info{display:flex;align-items:center;gap:12px}
        .dbx-book-cover{width:40px;height:54px;border-radius:6px;background:linear-gradient(135deg,#0f4c5c,#1a6b7c);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0;overflow:hidden}
        .dbx-book-details h4,.dbx-member-name{font-size:14px;font-weight:600;color:#1a2e35;margin-bottom:2px}
        .dbx-book-details span,.dbx-member-meta{font-size:12px;color:#5a6d73}
        .dbx-member-avatar{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#c4956a,#d4a574);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:11px;flex-shrink:0}
        .dbx-status{display:inline-flex;align-items:center;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:500}
        .dbx-status.active{background:rgba(45,134,89,.1);color:#2d8659}
        .dbx-status.pending{background:rgba(212,160,58,.1);color:#d4a03a}
        .dbx-status.overdue{background:rgba(196,69,54,.1);color:#c44536}
        .dbx-side{display:flex;flex-direction:column;gap:24px}
        .dbx-quick{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
        .dbx-quick-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:20px 16px;background:#f8f6f1;border:1px solid #e2ddd4;border-radius:12px;text-decoration:none;transition:.2s;color:#1a2e35}
        .dbx-quick-btn:hover{background:#c4956a;border-color:#c4956a;transform:translateY(-2px);color:#fff}
        .dbx-quick-btn i{width:24px;height:24px;color:#c4956a}.dbx-quick-btn:hover i{color:#fff}
        .dbx-quick-btn span{font-size:12px;font-weight:500;text-align:center}
        .dbx-popular-item,.dbx-activity-item{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #e2ddd4}
        .dbx-popular-item:last-child,.dbx-activity-item:last-child{border-bottom:none}
        .dbx-rank{width:28px;height:28px;border-radius:8px;background:#f8f6f1;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#5a6d73;flex-shrink:0}
        .dbx-rank.top{background:linear-gradient(135deg,#c4956a,#d4a574);color:#fff}
        .dbx-popular-cover{width:36px;height:48px;border-radius:6px;flex-shrink:0;background:linear-gradient(135deg,#0f4c5c,#1a6b7c);overflow:hidden}
        .dbx-popular-info{flex:1;min-width:0}
        .dbx-popular-info h4{font-size:13px;font-weight:600;color:#1a2e35;margin-bottom:2px}
        .dbx-popular-info span{font-size:11px;color:#5a6d73}
        .dbx-borrow-count{font-size:12px;font-weight:600;color:#0f4c5c;background:rgba(15,76,92,.08);padding:4px 8px;border-radius:6px;flex-shrink:0}
        .dbx-activity-icon{width:36px;height:36px;border-radius:10px;background:#f8f6f1;display:flex;align-items:center;justify-content:center;color:#0f4c5c;flex-shrink:0}
        .dbx-activity-content{min-width:0}
        .dbx-activity-content h4{font-size:13px;font-weight:600;color:#1a2e35}
        .dbx-activity-content p{font-size:12px;color:#5a6d73;line-height:1.5;margin-top:2px}
        .dbx-activity-content span{display:block;font-size:11px;color:#5a6d73;margin-top:4px}
        .dbx-activity-meta{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:6px}
        .dbx-activity-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700}
        .dbx-activity-badge.create{background:rgba(45,134,89,.1);color:#2d8659}
        .dbx-activity-badge.update{background:rgba(15,76,92,.08);color:#0f4c5c}
        .dbx-activity-badge.delete{background:rgba(196,69,54,.1);color:#c44536}
        .dbx-activity-module{font-size:11px;color:#5a6d73;text-transform:capitalize}
        .dbx-drawer-alert{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:12px;background:rgba(212,160,58,.12);border:1px solid rgba(212,160,58,.3);color:#d4a03a;font-size:12px;margin-bottom:18px;line-height:1.5}
        .dbx-drawer-alert i{flex-shrink:0}
        .dbx-drawer-alert strong{font-weight:700;color:#1a2e35}
        .chatbot-fab{position:fixed;right:18px;bottom:18px;width:54px;height:54px;border-radius:18px;border:1px solid rgba(196,149,106,.25);background:#fff;color:var(--accent);display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 18px 40px rgba(87,59,33,.14);transition:.25s cubic-bezier(.4,0,.2,1);z-index:160}
        .chatbot-fab:hover{transform:translateY(-2px);box-shadow:0 22px 50px rgba(87,59,33,.18)}
        .chatbot-panel{position:fixed;right:18px;bottom:86px;width:min(380px,calc(100vw - 36px));height:520px;background:var(--bg-raised);border:1px solid var(--border);border-radius:22px;box-shadow:0 28px 70px rgba(0,0,0,.18);opacity:0;pointer-events:none;transform:translateY(10px) scale(.98);transition:.22s cubic-bezier(.4,0,.2,1);z-index:160;display:flex;flex-direction:column;overflow:hidden}
        .chatbot-panel.open{opacity:1;pointer-events:auto;transform:translateY(0) scale(1)}
        .chatbot-head{padding:14px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(180deg,#fffdf9,#fbf8f2)}
        .chatbot-title{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:800;color:var(--fg)}
        .chatbot-actions{display:flex;align-items:center;gap:8px}
        .chatbot-close{width:34px;height:34px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--muted);cursor:pointer}
        .chatbot-menu{position:relative}
        .chatbot-menu-btn{width:34px;height:34px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--muted);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s}
        .chatbot-menu-btn:hover{color:var(--accent);border-color:rgba(196,149,106,.45);background:rgba(196,149,106,.06)}
        .chatbot-menu-pop{position:absolute;right:0;top:42px;min-width:190px;background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 18px 50px rgba(0,0,0,.16);padding:8px;display:none;z-index:180}
        .chatbot-menu-pop.open{display:block}
        .chatbot-menu-item{width:100%;display:flex;align-items:center;gap:10px;padding:10px 10px;border-radius:12px;border:none;background:transparent;color:var(--fg);font-size:13px;font-weight:700;cursor:pointer;text-align:left;transition:.18s}
        .chatbot-menu-item:hover{background:rgba(196,149,106,.08)}
        .chatbot-menu-item.danger{color:var(--red)}
        .chatbot-menu-item.danger:hover{background:rgba(196,69,54,.08)}
        .chatbot-menu-item i{width:16px;height:16px}
        .chatbot-messages{flex:1;overflow:auto;padding:16px 14px;display:flex;flex-direction:column;gap:10px}
        .chatbot-bubble{max-width:86%;padding:10px 12px;border-radius:16px;font-size:13px;line-height:1.5;white-space:pre-line}
        .chatbot-bubble.user{align-self:flex-end;background:rgba(15,76,92,.10);color:var(--fg);border:1px solid rgba(15,76,92,.14)}
        .chatbot-bubble.bot{align-self:flex-start;background:#fff;border:1px solid var(--border);color:var(--fg)}
        .chatbot-form{display:flex;gap:10px;padding:12px 12px;border-top:1px solid var(--border);background:#fff}
        .chatbot-input{flex:1;border:1px solid var(--border);border-radius:14px;padding:12px 12px;font-size:13px;background:var(--bg-soft)}
        .chatbot-input:focus{outline:none;border-color:rgba(196,149,106,.6);box-shadow:0 0 0 3px rgba(196,149,106,.14);background:#fff}
        .chatbot-send{width:46px;height:46px;border-radius:14px;border:1px solid rgba(196,149,106,.25);background:#fff;color:var(--accent);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:.2s}
        .chatbot-send:hover{background:rgba(196,149,106,.08);border-color:rgba(196,149,106,.45)}
        @media (max-width:1200px){.dbx-stats{grid-template-columns:repeat(2,1fr)}.dbx-content{grid-template-columns:1fr}.dbx-book-filters{grid-template-columns:1fr 1fr}}
        @media (max-width:900px){.dbx-book-filters,.dbx-book-form-grid,.dbx-borrower-stats,.dbx-drawer-book-meta,.dbx-borrower-profile{grid-template-columns:1fr}.dbx-book-section-head{display:flex;flex-direction:column;align-items:flex-start}}
        @media (max-width:640px){.dbx-stats,.dbx-quick,.dbx-book-grid{grid-template-columns:1fr}.dbx-welcome-title{font-size:24px}.dbx-card-body{padding:16px}.dbx-table td{padding:12px 8px}.dbx-drawer{width:100vw}}
    </style>
</head>
<body style="--brand-logo-bg: {{ $appColor }};">
    <div class="admin-shell">
        <div id="sideMask" class="side-mask" onclick="closeSide()"></div>

        <aside id="lightSide" class="sidebar open" aria-hidden="false">
            <div class="sidebar-logo">
                <div class="flex items-center gap-3">
                    <div class="sidebar-brand-mark">
                        @if ($appLogo)
                            <img src="{{ asset($appLogo) }}" alt="{{ $appName }}">
                        @else
                            <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                        @endif
                    </div>
                    <div class="font-display text-xl font-bold tracking-tight text-slate-800">{{ $appName }}</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                @foreach ($sidebarSections as $section)
                    <div class="nav-section">{{ $section['title'] }}</div>
                    @foreach ($section['items'] as $item)
                        <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="nav-icon"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <div class="sidebar-foot">
                <div class="sidebar-upgrade">
                    @if ($isPetugasPanel)
                        <div class="sidebar-focus-title">Fokus Petugas</div>
                        <p class="sidebar-focus-sub">Kelola peminjaman buku dengan cepat.</p>
                        <a href="{{ route('admin.loans.index') }}" class="sidebar-focus-link">
                            <span>Buka Peminjaman</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    @else
                        <div class="sidebar-focus-title">Admin Panel</div>
                        <p class="sidebar-focus-sub">Gaya modern LibraVault diaktifkan.</p>
                    @endif
                </div>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <div class="topbar-inner">
                    <div class="topbar-left">
                        <button
                            id="sideToggle"
                            class="hamburger"
                            type="button"
                            aria-label="Buka sidebar"
                            aria-controls="lightSide"
                            aria-expanded="false"
                            onclick="toggleSide()"
                        >=</button>
                        <div class="topbar-brand">
                            <div class="topbar-brand-mark">
                                @if ($appLogo)
                                    <img src="{{ asset($appLogo) }}" alt="{{ $appName }}">
                                @else
                                    <i data-lucide="book-open" style="width:18px;height:18px;color:#7A5A28;"></i>
                                @endif
                            </div>
                            <div class="topbar-brand-text">
                                <div class="topbar-brand-title font-display">{{ $appName }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="topbar-right">
                        <div class="notif-wrap">
                            <button
                                id="notifToggle"
                                class="topbar-btn notif-btn"
                                type="button"
                                aria-label="Buka notifikasi"
                                aria-expanded="false"
                                onclick="toggleNotifPanel()"
                            >
                                <i data-lucide="bell" class="w-5 h-5"></i>
                                <span id="headerNotifBadge" class="notif-badge" style="{{ $headerNotificationCount > 0 ? '' : 'display:none;' }}">
                                    {{ min($headerNotificationCount, 9) }}
                                </span>
                            </button>

                            <div id="notifPanel" class="notif-panel" aria-hidden="true">
                                <div class="notif-head">
                                    <div>
                                        <div class="notif-head-title">Notifikasi</div>
                                        <div id="headerNotifSub" class="notif-head-sub">{{ $headerNotificationCount }} update terbaru dari database</div>
                                    </div>
                                </div>
                                <div id="headerNotifList" class="notif-list">
                                    @forelse ($headerNotifications as $notification)
                                        <a href="{{ $notification['href'] ?? '#' }}" class="notif-item">
                                            <div class="notif-icon {{ $notification['tone'] }}">
                                                <i data-lucide="{{ $notification['icon'] ?? 'info' }}" class="w-4 h-4"></i>
                                            </div>
                                            <div class="notif-body">
                                                <div class="notif-title">{{ $notification['title'] }}</div>
                                                <div class="notif-text">{{ $notification['body'] }}</div>
                                                <div class="notif-time">{{ $notification['time'] ?? 'Baru saja' }}</div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="notif-empty">Belum ada notifikasi terbaru.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn-logout"><i data-lucide="log-out" class="w-4 h-4"></i>Logout</button></form>
                        <a href="{{ route('profile.show') }}" class="user-chip" style="text-decoration:none;">
                            <div class="avatar" style="overflow:hidden;">
                                @if (auth()->user()?->profile_photo_url)
                                    <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div style="font-size:12px;font-weight:700;color:var(--fg);line-height:1.2;">{{ auth()->user()->name }}</div>
                                <div style="font-size:10px;color:var(--dim);line-height:1.2;">{{ auth()->user()->role?->label ?? 'Tanpa role' }}</div>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            <main id="lightMain" class="page-wrap space-y-6">
                <div id="asyncToast" class="async-toast" aria-live="polite"></div>
                @if (session('status'))
                    <div class="alert-box alert-success">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert-box alert-error">{{ $errors->first() }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div id="chatbotRoot" data-user-id="{{ auth()->id() }}" data-endpoint="{{ route('chatbot.respond') }}">
        <button id="chatbotFab" class="chatbot-fab" type="button" aria-label="Chatbot" aria-expanded="false">
            <i data-lucide="message-circle" class="w-5 h-5"></i>
        </button>
        <div id="chatbotPanel" class="chatbot-panel" aria-hidden="true">
            <div class="chatbot-head">
                <div class="chatbot-title">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>ChatBot Perpus</span>
                </div>
                <div class="chatbot-actions">
                    <div class="chatbot-menu">
                        <button type="button" id="chatbotMenuBtn" class="chatbot-menu-btn" aria-label="Menu" aria-expanded="false">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                        <div id="chatbotMenuPop" class="chatbot-menu-pop" aria-hidden="true">
                            <button type="button" class="chatbot-menu-item danger" data-action="clear-chat">
                                <i data-lucide="trash-2"></i>
                                Hapus chat
                            </button>
                            <button type="button" class="chatbot-menu-item" data-action="close-chat">
                                <i data-lucide="x"></i>
                                Tutup
                            </button>
                        </div>
                    </div>
                    <button type="button" id="chatbotClose" class="chatbot-close" aria-label="Tutup">X</button>
                </div>
            </div>
            <div id="chatbotMessages" class="chatbot-messages"></div>
            <form id="chatbotForm" class="chatbot-form">
                <input id="chatbotInput" class="chatbot-input" placeholder="Tulis pesan..." autocomplete="off">
                <button class="chatbot-send" type="submit" aria-label="Kirim">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        const side = document.getElementById('lightSide');
        const sideMask = document.getElementById('sideMask');
        const sideToggle = document.getElementById('sideToggle');
        const notifPanel = document.getElementById('notifPanel');
        const notifToggle = document.getElementById('notifToggle');
        const headerNotifBadge = document.getElementById('headerNotifBadge');
        const headerNotifList = document.getElementById('headerNotifList');
        const headerNotifSub = document.getElementById('headerNotifSub');
        
        let seenSignatures = new Set();

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function showToast(notification) {
            // Check if dashboard has its own toast wrap, otherwise we might need a global one
            let toastWrap = document.getElementById('borrowerToastWrap');
            if (!toastWrap) {
                toastWrap = document.createElement('div');
                toastWrap.id = 'globalToastWrap';
                toastWrap.className = 'dbx-toast-wrap';
                document.body.appendChild(toastWrap);
            }

            const toast = document.createElement('div');
            toast.className = 'dbx-toast ' + (notification.tone || 'info');
            toast.innerHTML = '<div class="dbx-toast-title">' + escapeHtml(notification.title || 'Notifikasi baru') + '</div>'
                + '<div class="dbx-toast-body">' + escapeHtml(notification.body || '') + '</div>';
            toastWrap.appendChild(toast);

            requestAnimationFrame(function () {
                toast.classList.add('show');
            });

            window.setTimeout(function () {
                toast.classList.remove('show');
                window.setTimeout(function () {
                    toast.remove();
                }, 300);
            }, 4500);
        }

        async function refreshGlobalNotifications(isInitialLoad) {
            @php
                $canPoll = $user?->hasAnyPermission(['view_borrower_history', 'manage_loans']);
            @endphp
            const canPoll = @json($canPoll);
            if (!canPoll) return;

            try {
                const response = await fetch('{{ route('borrower.notifications') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return;

                const data = await response.json();
                const notifications = Array.isArray(data.notifications) ? data.notifications : [];
                
                // Update badge
                if (headerNotifBadge) {
                    if (notifications.length > 0) {
                        headerNotifBadge.style.display = 'flex';
                        headerNotifBadge.textContent = notifications.length > 9 ? '9+' : notifications.length;
                    } else {
                        headerNotifBadge.style.display = 'none';
                    }
                }

                // Update sub text
                if (headerNotifSub) {
                    headerNotifSub.textContent = notifications.length + ' update terbaru dari database';
                }

                // Update list
                if (headerNotifList) {
                    if (notifications.length === 0) {
                        headerNotifList.innerHTML = '<div class="notif-empty">Belum ada notifikasi terbaru.</div>';
                    } else {
                        headerNotifList.innerHTML = notifications.map(function (n) {
                            const icon = n.tone === 'danger' ? 'triangle-alert' : (n.tone === 'success' ? 'check-circle' : 'info');
                            return '<a href="#" class="notif-item">'
                                + '<div class="notif-icon ' + escapeHtml(n.tone || 'info') + '">'
                                + '<i data-lucide="' + icon + '" class="w-4 h-4"></i>'
                                + '</div>'
                                + '<div class="notif-body">'
                                + '<div class="notif-title">' + escapeHtml(n.title || 'Notifikasi') + '</div>'
                                + '<div class="notif-text">' + escapeHtml(n.body || '') + '</div>'
                                + '<div class="notif-time">Baru saja</div>'
                                + '</div>'
                                + '</a>';
                        }).join('');
                        
                        if (window.lucide) window.lucide.createIcons();
                    }
                }

                // Show toasts for new ones
                if (!isInitialLoad) {
                    notifications.forEach(n => {
                        if (n.signature && !seenSignatures.has(n.signature)) {
                            showToast(n);
                        }
                    });
                }

                // Update seen signatures
                seenSignatures = new Set(notifications.map(n => n.signature));

                // Dispatch event for dashboard-specific updates
                const event = new CustomEvent('notificationsUpdated', { detail: data });
                document.dispatchEvent(event);

            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        }

        function toggleNotifPanel() {
            const isOpen = notifPanel.classList.contains('show');
            if (isOpen) {
                notifPanel.classList.remove('show');
                notifPanel.setAttribute('aria-hidden', 'true');
                notifToggle.setAttribute('aria-expanded', 'false');
            } else {
                notifPanel.classList.add('show');
                notifPanel.setAttribute('aria-hidden', 'false');
                notifToggle.setAttribute('aria-expanded', 'true');
                closeSide();
            }
        }

        // Close panels when clicking outside
        document.addEventListener('click', function (event) {
            if (notifPanel.classList.contains('show') && !notifPanel.contains(event.target) && !notifToggle.contains(event.target)) {
                notifPanel.classList.remove('show');
                notifPanel.setAttribute('aria-hidden', 'true');
                notifToggle.setAttribute('aria-expanded', 'false');
            }
        });

        function setSideState(isOpen) {
            side.classList.toggle('open', isOpen);
            sideMask.classList.toggle('show', isOpen);
            side.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            sideToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function toggleSide() {
            setSideState(!side.classList.contains('open'));
        }

        function setNotifState(isOpen) {
            if (!notifPanel || !notifToggle) {
                return;
            }

            notifPanel.classList.toggle('show', isOpen);
            notifPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            notifToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function toggleNotifPanel() {
            setNotifState(!notifPanel.classList.contains('show'));
        }

        function closeSide() {
            setSideState(false);
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSide();
                setNotifState(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 1023) {
                closeSide();
            }
        });

        document.querySelectorAll('#lightSide .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 1023) {
                    closeSide();
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (!notifPanel || !notifToggle) {
                return;
            }

            if (!notifPanel.contains(event.target) && !notifToggle.contains(event.target)) {
                setNotifState(false);
            }
        });

        if (window.lucide) {
            window.lucide.createIcons();
        }

        const asyncToast = document.getElementById('asyncToast');
        let asyncToastTimer = null;

        function showAsyncToast(message, tone = 'success') {
            if (!asyncToast || !message) {
                return;
            }

            asyncToast.textContent = message;
            asyncToast.className = 'async-toast ' + tone;
            asyncToast.classList.add('show');

            if (asyncToastTimer) {
                window.clearTimeout(asyncToastTimer);
            }

            asyncToastTimer = window.setTimeout(function () {
                asyncToast.classList.remove('show');
            }, 3200);
        }

        async function refreshAsyncTargets(selectors) {
            if (!selectors || selectors.length === 0) {
                return;
            }

            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal memuat ulang data tampilan.');
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            selectors.forEach(function (selector) {
                const currentNode = document.querySelector(selector);
                const freshNode = doc.querySelector(selector);

                if (currentNode && freshNode) {
                    currentNode.replaceWith(freshNode);
                }
            });

            if (window.lucide) {
                window.lucide.createIcons();
            }

            document.dispatchEvent(new CustomEvent('async:refreshed', { detail: { selectors } }));
        }

        async function handleAsyncFormSubmit(form) {
            const confirmMessage = form.dataset.confirm;
            if (confirmMessage && !window.confirm(confirmMessage)) {
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            const originalLabel = submitButton ? submitButton.innerHTML : null;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalDisabled = submitButton.disabled ? '1' : '0';
                submitButton.innerHTML = form.dataset.loadingLabel || 'Memproses...';
            }

            try {
                const formData = new FormData(form);
                const isGet = (form.method || 'POST').toUpperCase() === 'GET';
                let url = form.action;
                
                let fetchOptions = {
                    method: isGet ? 'GET' : 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                };

                if (isGet) {
                    const params = new URLSearchParams(formData).toString();
                    url += (url.includes('?') ? '&' : '?') + params;
                } else {
                    fetchOptions.body = formData;
                }

                const response = await fetch(url, {
                    ...fetchOptions,
                    headers: {
                        ...fetchOptions.headers,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = data.message || Object.values(data.errors || {}).flat()[0] || 'Terjadi kesalahan.';
                    throw new Error(message);
                }

                if (form.dataset.removeClosest) {
                    const row = form.closest(form.dataset.removeClosest);
                    if (row) {
                        row.remove();
                    }
                }

                if (form.dataset.resetOnSuccess === 'true') {
                    form.reset();
                    // Reset custom file upload previews if any
                    form.querySelectorAll('.js-upload-preview').forEach(el => el.classList.remove('show'));
                    form.querySelectorAll('.file-upload-name').forEach(el => {
                        el.textContent = 'Belum ada file dipilih';
                        el.classList.add('is-empty');
                    });
                }

                if (form.dataset.successCall && typeof window[form.dataset.successCall] === 'function') {
                    window[form.dataset.successCall](data, form);
                }

                const refreshTargets = (form.dataset.refreshTargets || '')
                    .split(',')
                    .map(function (item) { return item.trim(); })
                    .filter(Boolean);

                if (refreshTargets.length > 0) {
                    await refreshAsyncTargets(refreshTargets);
                }

                // Update URL if it was a GET filter form
                if (isGet && form.dataset.updateUrl !== 'false') {
                    window.history.pushState({}, '', url);
                }

                showAsyncToast(data.message || 'Berhasil disimpan.', 'success');
            } catch (error) {
                showAsyncToast(error.message || 'Terjadi kesalahan.', 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    if (originalLabel !== null) {
                        submitButton.innerHTML = originalLabel;
                    }
                }
            }
        }

        const chatbotRoot = document.getElementById('chatbotRoot');
        const chatbotFab = document.getElementById('chatbotFab');
        const chatbotPanel = document.getElementById('chatbotPanel');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatbotMenuBtn = document.getElementById('chatbotMenuBtn');
        const chatbotMenuPop = document.getElementById('chatbotMenuPop');
        const chatbotMessages = document.getElementById('chatbotMessages');
        const chatbotForm = document.getElementById('chatbotForm');
        const chatbotInput = document.getElementById('chatbotInput');

        function defaultChatbotGreeting() {
            return 'Halo! Saya AI assistant di aplikasi ini. Kamu bisa tanya apa saja, misalnya pelajaran, ide, coding, ringkasan, atau hal terkait perpustakaan.';
        }

        function chatbotStorageKey() {
            const userId = chatbotRoot?.dataset?.userId || 'guest';
            return 'chatbot_history_' + userId;
        }

        function loadChatbotHistory() {
            try {
                const raw = localStorage.getItem(chatbotStorageKey());
                const parsed = raw ? JSON.parse(raw) : [];
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function saveChatbotHistory(items) {
            try {
                localStorage.setItem(chatbotStorageKey(), JSON.stringify(items.slice(-50)));
            } catch (e) {}
        }

        function appendChatbotBubble(role, text) {
            if (!chatbotMessages) return;
            const bubble = document.createElement('div');
            bubble.className = 'chatbot-bubble ' + (role === 'user' ? 'user' : 'bot');
            bubble.textContent = text;
            chatbotMessages.appendChild(bubble);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }

        function renderChatbotHistory() {
            if (!chatbotMessages) return;
            chatbotMessages.innerHTML = '';
            const history = loadChatbotHistory();
            if (history.length === 0) {
                appendChatbotBubble('bot', defaultChatbotGreeting());
                return;
            }
            history.forEach(item => appendChatbotBubble(item.role, item.text));
        }

        function setChatbotOpen(isOpen) {
            if (!chatbotPanel || !chatbotFab) return;
            chatbotPanel.classList.toggle('open', isOpen);
            chatbotPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            chatbotFab.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                renderChatbotHistory();
                setTimeout(() => chatbotInput?.focus(), 0);
            }
        }

        function setChatbotMenuOpen(isOpen) {
            if (!chatbotMenuPop || !chatbotMenuBtn) return;
            chatbotMenuPop.classList.toggle('open', isOpen);
            chatbotMenuPop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            chatbotMenuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen && window.lucide) {
                window.lucide.createIcons();
            }
        }

        if (chatbotFab) {
            chatbotFab.addEventListener('click', function () {
                setChatbotOpen(!chatbotPanel.classList.contains('open'));
            });
        }

        if (chatbotClose) {
            chatbotClose.addEventListener('click', function () {
                setChatbotOpen(false);
            });
        }

        if (chatbotMenuBtn) {
            chatbotMenuBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                setChatbotMenuOpen(!chatbotMenuPop.classList.contains('open'));
            });
        }

        if (chatbotMenuPop) {
            chatbotMenuPop.addEventListener('click', function (e) {
                const item = e.target.closest('[data-action]');
                if (!item) return;
                const action = item.dataset.action;

                if (action === 'clear-chat') {
                    try {
                        localStorage.removeItem(chatbotStorageKey());
                    } catch (e) {}
                    renderChatbotHistory();
                    if (typeof showAsyncToast === 'function') {
                        showAsyncToast('Chat berhasil dihapus.', 'success');
                    }
                    setChatbotMenuOpen(false);
                }

                if (action === 'close-chat') {
                    setChatbotMenuOpen(false);
                    setChatbotOpen(false);
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (chatbotMenuPop?.classList.contains('open')) {
                const clickedInsideMenu = event.target.closest('.chatbot-menu');
                if (!clickedInsideMenu) {
                    setChatbotMenuOpen(false);
                }
            }
        });

        if (chatbotForm) {
            chatbotForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const message = (chatbotInput?.value || '').trim();
                if (!message) return;

                const history = loadChatbotHistory();
                history.push({ role: 'user', text: message });
                saveChatbotHistory(history);
                appendChatbotBubble('user', message);
                chatbotInput.value = '';

                appendChatbotBubble('bot', 'Mengetik...');
                const typingNode = chatbotMessages?.lastElementChild;

                try {
                    const endpoint = chatbotRoot?.dataset?.endpoint;
                    const requestHistory = history.slice(-12);
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.content
                        },
                        body: JSON.stringify({
                            message,
                            history: requestHistory
                        })
                    });

                    const data = await response.json().catch(() => ({}));
                    const reply = data.reply || 'Maaf, terjadi kesalahan.';

                    if (typingNode) typingNode.remove();

                    const updated = loadChatbotHistory();
                    updated.push({ role: 'bot', text: reply });
                    saveChatbotHistory(updated);
                    appendChatbotBubble('bot', reply);
                } catch (error) {
                    if (typingNode) typingNode.remove();
                    const updated = loadChatbotHistory();
                    updated.push({ role: 'bot', text: 'Maaf, AI sedang tidak bisa dihubungi. Cek API key, model Gemini, quota, atau koneksi server lalu coba lagi.' });
                    saveChatbotHistory(updated);
                    appendChatbotBubble('bot', 'Maaf, AI sedang tidak bisa dihubungi. Cek API key, model Gemini, quota, atau koneksi server lalu coba lagi.');
                }
            });
        }

        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-async="true"]');
            if (!form) {
                return;
            }

            event.preventDefault();
            handleAsyncFormSubmit(form);
        });

        // Keep main navigation on normal page loads for reliability across devices/browsers.
        document.addEventListener('click', async function (event) {
            const link = event.target.closest('a[data-async="true"]');
            if (!link || !link.href || link.href.startsWith('#') || link.href.includes('javascript:void(0)')) {
                return;
            }

            const isSameOrigin = link.origin === window.location.origin;
            const isDownload = link.hasAttribute('download') || link.href.includes('export') || link.href.includes('download');

            if (!isSameOrigin || isDownload) {
                return;
            }

            event.preventDefault();
            
            const targetSelectors = (link.dataset.refreshTargets || '#lightMain, .member-page, .dbx')
                .split(',')
                .map(s => s.trim())
                .filter(Boolean);

            try {
                const response = await fetch(link.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                if (!response.ok) throw new Error('Gagal memuat data.');

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                targetSelectors.forEach(selector => {
                    const currentNode = document.querySelector(selector);
                    const freshNode = doc.querySelector(selector);
                    if (currentNode && freshNode) {
                        currentNode.replaceWith(freshNode);
                    }
                });

                window.history.pushState({}, '', link.href);
                if (window.lucide) window.lucide.createIcons();
                
                // Scroll to top of the refreshed area or page
                const firstTarget = document.querySelector(targetSelectors[0]);
                if (firstTarget) {
                    firstTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (error) {
                console.error('AJAX Navigation Error:', error);
                window.location.href = link.href; // Fallback to normal load
            }
        });

        // Initialize Global Notifications
        refreshGlobalNotifications(true);
        window.setInterval(function () {
            refreshGlobalNotifications(false);
        }, 15000);
    </script>
</body>
</html>
