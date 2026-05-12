<?php $__env->startSection('content'); ?>
<?php ($title = 'Dashboard'); ?>
<?php ($eyebrow = 'Ringkasan Sistem'); ?>

<style>
    #asyncDashboardWrap[data-dashboard-role="borrower"]{background-color:var(--dbx-bg, #f8fafc);position:relative;min-height:100vh;font-family:'Inter',ui-sans-serif,system-ui,sans-serif;color:var(--dbx-text, #1e293b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-pattern{position:absolute;inset:0;background-image:radial-gradient(#e2e8f0 1px, transparent 1px);background-size:20px 20px;opacity:.5;pointer-events:none;z-index:0}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-body{position:relative;z-index:1;padding:24px;max-width:1560px;margin:0 auto}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-welcome{margin-bottom:32px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-welcome-badge{display:inline-flex;align-items:center;gap:6px;background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600;margin-bottom:12px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-welcome-title{font-size:28px;font-weight:800;margin-bottom:4px;color:var(--dbx-text, #1e293b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-welcome-sub{font-size:15px;color:var(--dbx-text-muted, #64748b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-stat{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:16px;text-align:center}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-stat-value{font-size:24px;font-weight:800;color:var(--dbx-primary, #f97316)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-stat-label{font-size:12px;color:var(--dbx-text-muted, #64748b);margin-top:4px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-panel{background:linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);border:1px solid #ffedd5;border-radius:12px;padding:20px;margin-bottom:20px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-panel-title{font-size:12px;font-weight:700;color:var(--dbx-primary, #f97316);text-transform:uppercase;letter-spacing:.05em}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-panel-value{font-size:20px;font-weight:700;margin:8px 0 4px;color:var(--dbx-text, #1e293b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-panel-sub{font-size:13px;color:var(--dbx-text-muted, #64748b);margin-top:4px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-alert{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:12px;padding:14px 16px;font-size:13px;line-height:1.6;margin-bottom:20px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-notif-list{display:grid;gap:12px;margin-bottom:20px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-notif-item{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:14px 16px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-notif-title{font-size:13px;font-weight:700;color:var(--dbx-text, #1e293b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-notif-body{font-size:12px;color:var(--dbx-text-muted, #64748b);line-height:1.6;margin-top:4px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-card{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;overflow:hidden;box-shadow:none}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-card-header{padding:16px 20px;border-bottom:1px solid var(--dbx-border, #e2e8f0);background:#fafafa;display:flex;align-items:center;justify-content:space-between}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-card-title{font-size:16px;font-weight:700;color:var(--dbx-text, #1e293b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-card-body{padding:20px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-filter-field{width:100%;padding:10px 14px;border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;font-size:14px;outline:none;background:#fff;color:var(--dbx-text, #1e293b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-filter-field:focus{border-color:var(--dbx-primary, #f97316);box-shadow:0 0 0 3px rgba(249,115,22,.1)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-filter-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 16px;background:#fff;color:var(--dbx-text-muted, #64748b);border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-filter-btn:hover{border-color:var(--dbx-primary, #f97316);color:var(--dbx-primary, #f97316)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-section-head{margin:24px 0 16px;padding-bottom:12px;border-bottom:1px solid var(--dbx-border, #e2e8f0);display:flex;justify-content:space-between;align-items:center;gap:12px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-section-badge{background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:6px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-section-sub{font-size:13px;color:var(--dbx-text-muted, #64748b);margin-top:4px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:16px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-card{background:transparent;border:none;border-radius:0;overflow:visible;cursor:pointer;transition:transform .2s}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-card:hover{transform:translateY(-4px)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-thumb{height:225px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:18px;box-shadow:0 8px 20px rgba(15,23,42,.08);border:1px solid rgba(226,232,240,.9)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-thumb img{width:100%;height:100%;object-fit:cover}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-fallback{font-size:48px;font-weight:800;color:#cbd5e1}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-body{padding:12px 4px 0}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-chip{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;margin-bottom:8px;background:#dcfce7;color:#166534}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-chip.unavailable{background:#fee2e2;color:#991b1b}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-name{font-size:14px;font-weight:700;margin-bottom:2px;color:var(--dbx-text, #1e293b)}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-author{font-size:12px;color:var(--dbx-text-muted, #64748b);margin-bottom:8px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-meta{display:flex;justify-content:space-between;font-size:11px;color:var(--dbx-text-muted, #64748b);border-top:1px solid var(--dbx-border, #e2e8f0);padding-top:8px}
    #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-book-open{display:none}
    #borrowDrawerMask.dbx-drawer-mask{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:40;display:none}
    #borrowDrawer.dbx-drawer{position:fixed;top:0;right:0;width:100%;max-width:400px;height:100vh;background:#fff;z-index:50;transform:translateX(100%);transition:transform .3s;display:flex;flex-direction:column;box-shadow:-10px 0 20px rgba(0,0,0,.1)}
    #borrowDrawer.dbx-drawer.open{transform:translateX(0)}
    #borrowDrawer .dbx-drawer-head{padding:20px;border-bottom:1px solid var(--dbx-border, #e2e8f0);display:flex;justify-content:space-between;align-items:center}
    #borrowDrawer .dbx-drawer-title{font-size:20px;font-weight:700;color:var(--dbx-text, #1e293b)}
    #borrowDrawer .dbx-drawer-sub{font-size:13px;color:var(--dbx-text-muted, #64748b);margin-top:4px}
    #borrowDrawer .dbx-drawer-close{width:32px;height:32px;border:1px solid var(--dbx-border, #e2e8f0);background:#fff;border-radius:6px;cursor:pointer;font-weight:bold;color:var(--dbx-text-muted, #64748b)}
    #borrowDrawer .dbx-drawer-close:hover{background:var(--dbx-danger, #ef4444);border-color:var(--dbx-danger, #ef4444);color:#fff}
    #borrowDrawer .dbx-drawer-body{padding:20px}
    #borrowDrawer .dbx-drawer-book-title{font-size:18px;font-weight:700;color:var(--dbx-text, #1e293b)}
    #borrowDrawer .dbx-drawer-book-author,#borrowDrawer .dbx-drawer-book-label{font-size:12px;color:var(--dbx-text-muted, #64748b)}
    #borrowDrawer .dbx-drawer-book-value{font-size:13px;font-weight:600;color:var(--dbx-text, #1e293b)}
    #borrowDrawer .dbx-drawer-alert{display:flex;align-items:flex-start;gap:8px;padding:12px 14px;border-radius:10px;background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);font-size:12px;line-height:1.6;margin:16px 0}
    #borrowDrawer .dbx-book-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    #borrowDrawer .dbx-book-note{width:100%;margin-top:12px;padding:10px 14px;border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;font-size:13px;min-height:100px;resize:vertical}
    #borrowDrawer .dbx-book-submit{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 16px;background:var(--dbx-primary, #f97316);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;width:100%;margin-top:16px}
    #borrowDrawer .dbx-book-submit:hover{background:var(--dbx-primary-hover, #ea580c)}
    @media (max-width:1024px){
        #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-body{padding:24px 0}
    }
    @media (min-width:1400px){
        #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-body{padding-left:32px;padding-right:32px}
    }
    @media (max-width:900px){
        #asyncDashboardWrap[data-dashboard-role="borrower"] .dbx-borrower-stats{grid-template-columns:1fr}
    }
</style>

<div
    id="asyncDashboardWrap"
    class="dbx"
    data-dashboard-role="<?php echo e($isPrincipalDashboard ? 'principal' : ($isBorrowerDashboard ? 'borrower' : 'other')); ?>"
    data-principal-signatures="<?php echo e($isPrincipalDashboard ? $principalProcurements->map(fn ($procurement) => 'principal-procurement-'.$procurement->id.'-'.$procurement->status)->implode('|') : ''); ?>"
    data-borrower-signatures="<?php echo e($isBorrowerDashboard ? $borrowerNotifications->pluck('signature')->filter()->implode('|') : ''); ?>"
    data-borrower-state-signature="<?php echo e($isBorrowerDashboard ? ($borrowerSnapshot['signature'] ?? '') : ''); ?>"
>
    <div class="dbx-pattern"></div>
    <div class="dbx-body">
        <section class="dbx-welcome">
            <div>
                <div class="dbx-welcome-badge"><i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Dashboard</div>
                <div class="dbx-welcome-title">Hello, <?php echo e(auth()->user()?->name ?? 'Pengguna'); ?></div>
                <div class="dbx-welcome-sub">
                    <?php if($isBorrowerDashboard): ?>
                        <?php echo e($dashboardMeta['today_label']); ?>. Berikut daftar buku yang saat ini tersedia untuk dipinjam.
                    <?php else: ?>
                        <?php echo e($dashboardMeta['today_label']); ?>. Semoga aktivitas perpustakaan hari ini lancar.
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if($isBorrowerDashboard): ?>
            <section class="dbx-borrower-stats">
                <article class="dbx-borrower-stat">
                    <div class="dbx-borrower-stat-value" id="stat-requested"><?php echo e($borrowerLoanStats['requested']); ?></div>
                    <div class="dbx-borrower-stat-label">Pengajuan menunggu petugas</div>
                </article>
                <article class="dbx-borrower-stat">
                    <div class="dbx-borrower-stat-value" id="stat-borrowed"><?php echo e($borrowerLoanStats['borrowed']); ?></div>
                    <div class="dbx-borrower-stat-label">Buku sedang dipinjam</div>
                </article>
                <article class="dbx-borrower-stat">
                    <div class="dbx-borrower-stat-value" id="stat-returned"><?php echo e($borrowerLoanStats['returned']); ?></div>
                    <div class="dbx-borrower-stat-label">Riwayat selesai</div>
                </article>
            </section>

            <section class="dbx-borrower-profile">
                <article class="dbx-borrower-panel">
                    <div class="dbx-borrower-panel-title">Identitas Peminjam</div>
                    <div class="dbx-borrower-panel-value"><?php echo e(auth()->user()?->name ?? 'Peminjam'); ?></div>
                    <div class="dbx-borrower-panel-sub">
                        <?php echo e(auth()->user()?->role?->label ?? 'Anggota'); ?>

                        <?php if(auth()->user()?->academicLabel()): ?>
                            | <?php echo e(auth()->user()->academicLabel()); ?>

                        <?php endif; ?>
                    </div>
                    <div class="dbx-borrower-panel-sub" id="borrowerAccountStatus">
                        Status akun:
                        <strong><?php echo e($borrowerActiveSanction ? 'Sedang kena sanksi' : 'Aktif'); ?></strong>
                    </div>
                </article>
            </section>

            <?php if($borrowerActiveSanction): ?>
                <div class="dbx-borrower-alert" id="borrowerSanctionAlert">
                    Akun Anda sedang disanksi dan belum bisa mengajukan pinjam.
                    <?php if($borrowerActiveSanction->ends_at): ?>
                        Masa sanksi sampai <?php echo e($borrowerActiveSanction->ends_at->translatedFormat('d M Y')); ?>.
                    <?php endif; ?>
                    Anda harus menunggu sampai masa sanksi selesai sebelum bisa pinjam lagi.
                </div>
            <?php else: ?>
                <div class="dbx-borrower-alert" id="borrowerSanctionAlert" style="display:none;"></div>
            <?php endif; ?>

            <?php if($borrowerNotifications->isNotEmpty()): ?>
                <section class="dbx-notif-list" id="borrowerNotificationList">
                    <?php $__currentLoopData = $borrowerNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="dbx-notif-item <?php echo e($notification['tone']); ?>" data-signature="<?php echo e($notification['signature']); ?>">
                            <div class="dbx-notif-title"><?php echo e($notification['title']); ?></div>
                            <div class="dbx-notif-body"><?php echo e($notification['body']); ?></div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </section>
            <?php else: ?>
                <section class="dbx-notif-list" id="borrowerNotificationList" style="display:none;"></section>
            <?php endif; ?>

            <section class="dbx-card">
                <div class="dbx-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <h3 class="dbx-card-title">Cari Buku</h3>
                    <div class="dbx-book-section-badge" style="margin-left:auto;">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Cari di depan
                    </div>
                </div>
                <div class="dbx-card-body">
                    <?php $__errorArgs = ['loan_request'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="dbx-borrower-alert" style="margin-bottom:18px;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="dbx-book-filters" id="borrowerBookFilterForm" data-async="true" data-refresh-targets="#asyncDashboardWrap" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:14px;">
                        <input
                            type="text"
                            name="q"
                            id="borrowerBookKeyword"
                            class="dbx-book-filter-field"
                            style="flex:1 1 260px;min-width:220px;"
                            value="<?php echo e($bookFilters['keyword']); ?>"
                            placeholder="Cari judul atau penulis"
                            autocomplete="off"
                        >
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                            <select name="category" id="borrowerBookCategoryFilter" class="dbx-book-filter-field" style="flex:0 1 180px;min-width:160px;">
                                <option value="">Semua kategori</option>
                                <?php $__currentLoopData = $borrowerCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->slug); ?>" <?php if($bookFilters['category'] === $category->slug): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <select name="availability" id="borrowerBookAvailabilityFilter" class="dbx-book-filter-field" style="flex:0 0 140px;min-width:140px;">
                                <option value="available" <?php if($bookFilters['availability'] === 'available'): echo 'selected'; endif; ?>>Tersedia</option>
                                <option value="all" <?php if($bookFilters['availability'] === 'all'): echo 'selected'; endif; ?>>Semua buku</option>
                            </select>
                            <div style="position:relative;display:inline-flex;align-items:center;">
                                <button type="button" id="borrowerImageSearchSourceBtn" class="dbx-book-filter-btn" style="width:48px;height:48px;padding:0;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;" aria-label="Pilih foto buku">
                                    <i data-lucide="camera" class="w-5 h-5"></i>
                                </button>
                                <div id="borrowerImageSearchSourceMenu" style="display:none;position:absolute;top:58px;right:0;z-index:20;min-width:210px;padding:10px;border:1px solid rgba(196,149,106,.18);border-radius:16px;background:#fff;box-shadow:0 16px 30px rgba(15,76,92,.12);">
                                    <button type="button" id="borrowerImageSearchCamera" class="dbx-book-filter-btn" style="width:100%;justify-content:flex-start;margin-bottom:8px;">Foto Langsung</button>
                                    <button type="button" id="borrowerImageSearchGallery" class="dbx-book-filter-btn" style="width:100%;justify-content:flex-start;">Dari File / Galeri</button>
                                </div>
                            </div>
                            <button type="submit" class="dbx-book-filter-btn" style="min-width:96px;height:48px;padding:0 14px;white-space:nowrap;"><i data-lucide="search" class="w-4 h-4"></i>Cari</button>
                        </div>
                    </form>

                    <input type="file" id="borrowerImageSearchCameraInput" name="image" accept="image/*" capture="environment" style="display:none;">
                    <input type="file" id="borrowerImageSearchGalleryInput" name="image" accept="image/*" style="display:none;">

                    <div id="borrowerCameraModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:#000;z-index:9999;flex-direction:column;align-items:center;justify-content:center;">
                        <video id="borrowerCameraVideo" autoplay playsinline style="width:100%;height:80%;object-fit:cover;"></video>
                        <div style="height:20%;display:flex;align-items:center;justify-content:space-around;width:100%;padding:20px;">
                            <button type="button" id="borrowerCameraCloseBtn" style="color:#fff;background:transparent;border:none;font-size:16px;padding:10px;">Batal</button>
                            <button type="button" id="borrowerCameraCaptureBtn" style="width:64px;height:64px;border-radius:50%;background:#fff;border:4px solid #ccc;cursor:pointer;"></button>
                            <div style="width:50px;"></div>
                        </div>
                        <canvas id="borrowerCameraCanvas" style="display:none;"></canvas>
                    </div>

                    <div id="borrowerImageSearchPreviewWrap" style="display:none;align-items:center;justify-content:center;min-height:140px;margin-top:14px;border:1px dashed rgba(196,149,106,.22);border-radius:18px;background:#fff;overflow:hidden;">
                        <img id="borrowerImageSearchPreview" alt="Preview foto buku" style="max-width:100%;max-height:220px;object-fit:contain;">
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                        <div id="borrowerImageSearchFileName" style="font-size:13px;color:var(--muted);display:none;"></div>
                        <div id="borrowerImageSearchActions" style="display:none;gap:8px;">
                            <button type="button" id="borrowerImageSearchActionBtn" class="dbx-book-filter-btn" style="height:36px;padding:0 14px;white-space:nowrap;font-size:13px;"><i data-lucide="search" class="w-4 h-4"></i>Cari Buku</button>
                            <button type="button" id="borrowerImageSearchClearBtn" class="dbx-book-filter-btn" style="height:36px;padding:0 14px;white-space:nowrap;font-size:13px;background:var(--red-light);color:var(--red);border-color:transparent;"><i data-lucide="trash-2" class="w-4 h-4"></i>Hapus</button>
                        </div>
                    </div>
                    <div id="borrowerImageSearchStatus" style="font-size:13px;color:var(--muted);margin-top:8px;"></div>
                    <div id="borrowerImageSearchResults" style="display:none;margin-top:16px;"></div>

                    <div class="dbx-book-section-head">
                        <div class="dbx-book-section-copy">
                            <div class="dbx-card-title">Koleksi Pilihan</div>
                            <div class="dbx-book-section-sub">Tampilan dibuat seperti etalase buku: cover lebih dominan, info singkat di bawah, lalu klik satu buku untuk buka panel pinjam dari samping.</div>
                        </div>
                        <div class="dbx-book-section-badge">
                            <i data-lucide="panel-right-open" class="w-4 h-4"></i>
                            Klik cover untuk pinjam
                        </div>
                    </div>

                    <div class="dbx-book-showcase">
                        <div class="dbx-book-grid-wrap">
                            <div class="dbx-book-grid" id="borrowerBookGrid">
                                <?php $__empty_1 = true; $__currentLoopData = $borrowerBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <article
                                        class="dbx-book-card js-borrow-book"
                                        role="button"
                                        tabindex="0"
                                        data-id="<?php echo e($book->id); ?>"
                                        data-title="<?php echo e($book->title); ?>"
                                        data-author="<?php echo e($book->author ?? 'Penulis tidak tersedia'); ?>"
                                        data-category="<?php echo e($book->category?->name ?? 'Tanpa kategori'); ?>"
                                        data-stock="<?php echo e($book->stock_available); ?>"
                                        data-cover-url="<?php echo e($book->cover_image ? asset('storage/'.$book->cover_image) : ''); ?>"
                                        data-borrowed-at="<?php echo e(now()->toDateString()); ?>"
                                        data-due-at="<?php echo e(now()->addDay()->toDateString()); ?>"
                                        data-borrow-state="<?php echo e($book->borrow_state ?? ($borrowerActiveSanction ? 'sanctioned' : ($book->stock_available > 0 ? 'available' : 'unavailable'))); ?>"
                                        data-can-borrow="<?php echo e(($book->can_borrow ?? ($book->stock_available > 0 && ! $borrowerActiveSanction)) ? '1' : '0'); ?>"
                                    >
                                        <div class="dbx-book-thumb">
                                            <?php if($book->cover_image): ?>
                                                <img src="<?php echo e(asset('storage/'.$book->cover_image)); ?>" alt="<?php echo e($book->title); ?>">
                                            <?php else: ?>
                                                <div class="dbx-book-fallback"><?php echo e(strtoupper(substr($book->title, 0, 1))); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dbx-book-body">
                                            <div class="dbx-book-chip <?php echo e(($book->borrow_state ?? '') !== 'available' ? 'unavailable' : ''); ?>">
                                                <?php echo e(($book->borrow_state ?? null) === 'requested'
                                                        ? 'Menunggu petugas'
                                                        : (($book->borrow_state ?? null) === 'borrowed'
                                                            ? 'Sedang dipinjam'
                                                        : (($book->borrow_state ?? null) === 'sanctioned'
                                                            ? 'Akun disanksi'
                                                            : (($book->borrow_state ?? null) === 'available' ? 'Tersedia' : 'Habis')))); ?>

                                            </div>
                                            <div class="dbx-book-name"><?php echo e($book->title); ?></div>
                                            <div class="dbx-book-author"><?php echo e($book->author ?? 'Penulis tidak tersedia'); ?></div>
                                            <div class="dbx-book-meta">
                                                <span class="dbx-book-stock"><?php echo e($book->stock_available); ?> stok</span>
                                                <span><?php echo e($book->category?->name ?? 'Umum'); ?></span>
                                            </div>
                                            <div class="dbx-book-open">
                                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                Lihat
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="text-sm text-slate2-400" id="borrowerBookEmpty">Buku yang dicari belum ditemukan.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif($isPrincipalDashboard): ?>
        <section class="dbx-stats">
            <article class="dbx-stat members">
                <div class="dbx-stat-icon"><i data-lucide="users"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($principalMetrics['petugas_active_today'])); ?></div>
                <div class="dbx-stat-label">Petugas Aktif Hari Ini</div>
                <div class="dbx-stat-trend up"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i><?php echo e($principalMetrics['petugas_actions_today']); ?> aktivitas tercatat</div>
            </article>
            <article class="dbx-stat books">
                <div class="dbx-stat-icon"><i data-lucide="library-big"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($principalMetrics['books_growth'])); ?></div>
                <div class="dbx-stat-label">Buku Baru 30 Hari</div>
                <div class="dbx-stat-trend up"><i data-lucide="book-plus" class="w-3.5 h-3.5"></i>Perkembangan koleksi perpustakaan</div>
            </article>
            <article class="dbx-stat borrowed">
                <div class="dbx-stat-icon"><i data-lucide="book-up-2"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($principalMetrics['loans_growth'])); ?></div>
                <div class="dbx-stat-label">Transaksi 30 Hari</div>
                <div class="dbx-stat-trend up"><i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>Aktivitas layanan perpustakaan</div>
            </article>
            <article class="dbx-stat overdue">
                <div class="dbx-stat-icon"><i data-lucide="activity"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($principalMetrics['service_score'])); ?></div>
                <div class="dbx-stat-label">Skor Layanan</div>
                <div class="dbx-stat-trend <?php echo e($principalMetrics['service_score'] >= 80 ? 'up' : 'down'); ?>"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i>Cek apakah layanan berjalan baik</div>
            </article>
        </section>

        <section class="dbx-content">
            <article class="dbx-card">
                <div class="dbx-card-header">
                    <h3 class="dbx-card-title">Monitoring Kinerja</h3>
                </div>
                <div class="dbx-card-body">
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                            <div class="text-sm font-semibold text-slate2-900">Melihat aktivitas petugas</div>
                            <div class="mt-2 text-3xl font-bold text-slate2-900"><?php echo e(number_format($principalMetrics['petugas_actions_today'])); ?></div>
                            <div class="mt-2 text-sm text-slate2-600">Aktivitas petugas hari ini tercatat dari setiap aksi input, update, dan proses layanan.</div>
                        </div>
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                            <div class="text-sm font-semibold text-slate2-900">Melihat perkembangan perpustakaan</div>
                            <div class="mt-2 text-3xl font-bold text-slate2-900"><?php echo e(number_format($principalMetrics['books_growth'])); ?></div>
                            <div class="mt-2 text-sm text-slate2-600">Penambahan koleksi dalam 30 hari terakhir dan <?php echo e(number_format($principalMetrics['loans_growth'])); ?> transaksi layanan berjalan.</div>
                        </div>
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                            <div class="text-sm font-semibold text-slate2-900">Cek apakah layanan berjalan baik</div>
                            <div class="mt-2 text-3xl font-bold text-slate2-900"><?php echo e(number_format($principalMetrics['returned_today'])); ?></div>
                            <div class="mt-2 text-sm text-slate2-600">Buku kembali hari ini <?php echo e($principalMetrics['returned_today']); ?>, permintaan menunggu <?php echo e($principalMetrics['pending_requests']); ?>, terlambat <?php echo e($principalMetrics['late_loans']); ?>.</div>
                        </div>
                    </div>
                </div>
            </article>

            <div class="dbx-side">
                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Aktivitas Petugas</h3>
                    </div>
                    <div class="dbx-card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $principalActivityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="dbx-activity-item">
                                <div class="dbx-activity-icon"><i data-lucide="briefcase-business"></i></div>
                                <div class="dbx-activity-content">
                                    <h4><?php echo e($activity->user?->name ?? 'Petugas'); ?></h4>
                                    <p><?php echo e($activity->description); ?></p>
                                    <div class="dbx-activity-meta">
                                        <span class="dbx-activity-badge update"><?php echo e(ucfirst($activity->action)); ?></span>
                                        <span class="dbx-activity-module"><?php echo e(str_replace('_', ' ', $activity->module)); ?></span>
                                    </div>
                                    <span><?php echo e($activity->created_at->diffForHumans()); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-slate2-400">Belum ada aktivitas petugas yang tercatat hari ini.</p>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Kondisi Layanan</h3>
                    </div>
                    <div class="dbx-card-body">
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                                <div class="text-sm font-semibold text-slate2-900">Permintaan Menunggu</div>
                                <div class="mt-2 text-2xl font-bold text-slate2-900"><?php echo e($principalMetrics['pending_requests']); ?></div>
                                <div class="mt-2 text-sm text-slate2-600">Semakin kecil angka ini, semakin cepat layanan petugas diproses.</div>
                            </div>
                            <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                                <div class="text-sm font-semibold text-slate2-900">Keterlambatan Aktif</div>
                                <div class="mt-2 text-2xl font-bold text-slate2-900"><?php echo e($principalMetrics['late_loans']); ?></div>
                                <div class="mt-2 text-sm text-slate2-600">Dipakai untuk melihat apakah layanan pengembalian dan pengawasan berjalan baik.</div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Persetujuan Pengadaan Buku</h3>
                    </div>
                    <div class="dbx-card-body">
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4 mb-4">
                            <div class="text-sm font-semibold text-slate2-900">Usulan Menunggu</div>
                            <div class="mt-2 text-2xl font-bold text-slate2-900"><?php echo e($principalMetrics['pending_procurements']); ?></div>
                            <div class="mt-2 text-sm text-slate2-600">Melihat usulan buku baru dan menyetujui pembelian atau penambahan koleksi.</div>
                        </div>

                        <div class="space-y-4">
                            <?php $__empty_1 = true; $__currentLoopData = $principalProcurements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $procurement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="rounded-2xl border border-slate2-100 bg-white p-4 js-principal-procurement-card">
                                    <div class="text-sm font-semibold text-slate2-900"><?php echo e($procurement->title); ?></div>
                                    <div class="mt-1 text-sm text-slate2-600"><?php echo e($procurement->author); ?> | Jumlah <?php echo e($procurement->quantity); ?></div>
                                    <div class="mt-2 text-sm text-slate2-600">
                                        Pengusul <?php echo e($procurement->proposer?->name ?? 'Petugas'); ?>

                                        <?php if($procurement->category?->name): ?>
                                            | <?php echo e($procurement->category->name); ?>

                                        <?php endif; ?>
                                        <?php if($procurement->notes): ?>
                                            | <?php echo e($procurement->notes); ?>

                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <form method="POST" action="<?php echo e(route('admin.books.procurements.approve', $procurement)); ?>" data-async="true" data-remove-closest=".js-principal-procurement-card" data-refresh-targets="#asyncDashboardWrap">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <button class="btn-primary rounded-xl px-4 py-2 text-sm font-semibold" type="submit">Setujui Pengadaan</button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('admin.books.procurements.reject', $procurement)); ?>" data-async="true" data-remove-closest=".js-principal-procurement-card" data-refresh-targets="#asyncDashboardWrap">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <button class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" type="submit">Tolak</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-sm text-slate2-400">Belum ada usulan buku baru yang menunggu persetujuan.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>


            </div>
        </section>
        <?php else: ?>
        <section class="dbx-stats">
            <article class="dbx-stat books">
                <div class="dbx-stat-icon"><i data-lucide="book-open"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($stats['books'])); ?></div>
                <div class="dbx-stat-label">Total Koleksi Buku</div>
                <div class="dbx-stat-trend up"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>Data katalog aktif</div>
            </article>
            <article class="dbx-stat members">
                <div class="dbx-stat-icon"><i data-lucide="users"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($stats['members'])); ?></div>
                <div class="dbx-stat-label">Anggota Aktif</div>
                <div class="dbx-stat-trend up"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>Role peminjam aktif</div>
            </article>
            <article class="dbx-stat borrowed">
                <div class="dbx-stat-icon"><i data-lucide="book-up-2"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($stats['borrowed'])); ?></div>
                <div class="dbx-stat-label">Sedang Dipinjam</div>
                <div class="dbx-stat-trend up"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>Transaksi berjalan</div>
            </article>
            <article class="dbx-stat overdue">
                <div class="dbx-stat-icon"><i data-lucide="triangle-alert"></i></div>
                <div class="dbx-stat-value"><?php echo e(number_format($stats['late'])); ?></div>
                <div class="dbx-stat-label">Terlambat Kembali</div>
                <div class="dbx-stat-trend down"><i data-lucide="trending-down" class="w-3.5 h-3.5"></i>Perlu tindak lanjut</div>
            </article>
        </section>

        <section class="dbx-content">
            <article class="dbx-card">
                <div class="dbx-card-header">
                    <h3 class="dbx-card-title">Peminjaman Terbaru</h3>
                    <a href="<?php echo e(route('admin.loans.index')); ?>" class="dbx-card-action">Lihat Semua</a>
                </div>
                <div class="dbx-table-wrap">
                    <table class="dbx-table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Peminjam</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php ($status = $loan->status === 'late' ? 'overdue' : ($loan->status === 'returned' ? 'pending' : 'active')); ?>
                                <?php ($memberName = $loan->member?->name ?? 'Anggota tidak ditemukan'); ?>
                                <tr>
                                    <td>
                                        <div class="dbx-book-info">
                                            <div class="dbx-book-cover">
                                                <?php if($loan->book?->cover_image): ?>
                                                    <img src="<?php echo e(asset('storage/'.$loan->book->cover_image)); ?>" alt="<?php echo e($loan->book?->title); ?>" style="width:100%;height:100%;object-fit:cover;">
                                                <?php else: ?>
                                                    <?php echo e(strtoupper(substr($loan->book?->title ?? 'B', 0, 1))); ?>

                                                <?php endif; ?>
                                            </div>
                                            <div class="dbx-book-details">
                                                <h4><?php echo e($loan->book?->title ?? 'Buku tidak ditemukan'); ?></h4>
                                                <span><?php echo e($loan->book?->author ?? 'Penulis tidak tersedia'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dbx-member-info">
                                            <div class="dbx-member-avatar"><?php echo e(strtoupper(substr($memberName, 0, 2))); ?></div>
                                            <div>
                                                <div class="dbx-member-name"><?php echo e($memberName); ?></div>
                                                <div class="dbx-member-meta">
                                                    <?php echo e($loan->member?->academicLabel() ?: ($loan->member?->email ?? 'Tanpa email')); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e(optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-'); ?></td>
                                    <td>
                                        <span class="dbx-status <?php echo e($status); ?>">
                                            <?php echo e($loan->status === 'late' ? 'Terlambat' : ($loan->status === 'returned' ? 'Dikembalikan' : 'Dipinjam')); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-slate2-400">Belum ada data peminjaman.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <div class="dbx-side">
                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Buku Terpopuler</h3>
                        <a href="<?php echo e(route('admin.books.index')); ?>" class="dbx-card-action">Lihat Semua</a>
                    </div>
                    <div class="dbx-card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $popularBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="dbx-popular-item">
                                <div class="dbx-rank <?php echo e($index < 3 ? 'top' : ''); ?>"><?php echo e($index + 1); ?></div>
                                <div class="dbx-popular-cover" style="background:linear-gradient(135deg,hsl(<?php echo e(180 + ($index * 20)); ?>,40%,35%),hsl(<?php echo e(180 + ($index * 20)); ?>,40%,45%));">
                                    <?php if($book->cover_image): ?>
                                        <img src="<?php echo e(asset('storage/'.$book->cover_image)); ?>" alt="<?php echo e($book->title); ?>" style="width:100%;height:100%;object-fit:cover;">
                                    <?php endif; ?>
                                </div>
                                <div class="dbx-popular-info">
                                    <h4><?php echo e($book->title); ?></h4>
                                    <span><?php echo e($book->author ?? 'Penulis tidak tersedia'); ?></span>
                                </div>
                                <div class="dbx-borrow-count"><?php echo e(number_format($book->loans_count)); ?>x</div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-slate2-400">Belum ada buku populer. Data akan muncul setelah ada peminjaman.</p>
                        <?php endif; ?>
                    </div>
                </article>

                <?php if($canViewActivityLog): ?>
                    <article class="dbx-card">
                        <div class="dbx-card-header">
                            <h3 class="dbx-card-title">Aktivitas Terbaru</h3>
                        </div>
                        <div class="dbx-card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php ($actionLabels = ['create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus']); ?>
                                <?php ($actionIcons = ['create' => 'plus', 'update' => 'pencil', 'delete' => 'trash-2']); ?>
                                <div class="dbx-activity-item">
                                    <div class="dbx-activity-icon"><i data-lucide="<?php echo e($actionIcons[$activity->action] ?? 'history'); ?>"></i></div>
                                    <div class="dbx-activity-content">
                                        <h4><?php echo e($activity->user?->name ?? 'Sistem'); ?></h4>
                                        <p><?php echo e($activity->description); ?></p>
                                        <div class="dbx-activity-meta">
                                            <span class="dbx-activity-badge <?php echo e($activity->action); ?>"><?php echo e($actionLabels[$activity->action] ?? ucfirst($activity->action)); ?></span>
                                            <span class="dbx-activity-module"><?php echo e(str_replace('_', ' ', $activity->module)); ?></span>
                                        </div>
                                        <span><?php echo e($activity->created_at->diffForHumans()); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-sm text-slate2-400">Belum ada aktivitas super admin seperti tambah, ubah, atau hapus data.</p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if($isSuperAdminDashboard): ?>
                    <article class="dbx-card">
                        <div class="dbx-card-header">
                            <h3 class="dbx-card-title">Hasil Pengadaan Buku</h3>
                        </div>
                        <div class="dbx-card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $superAdminProcurementUpdates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $procurement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php ($isRejected = $procurement->status === 'rejected'); ?>
                                <div class="dbx-activity-item">
                                    <div class="dbx-activity-icon">
                                        <i data-lucide="<?php echo e($isRejected ? 'circle-x' : 'badge-check'); ?>"></i>
                                    </div>
                                    <div class="dbx-activity-content">
                                        <h4><?php echo e($procurement->title); ?></h4>
                                        <p>
                                            Usulan dari <?php echo e($procurement->proposer?->name ?? 'Petugas'); ?>

                                            <?php echo e($isRejected ? 'ditolak' : 'disetujui'); ?>

                                            oleh <?php echo e($isRejected ? ($procurement->rejector?->name ?? 'Pemeriksa') : ($procurement->approver?->name ?? 'Pemeriksa')); ?>.
                                        </p>
                                        <div class="dbx-activity-meta">
                                            <span class="dbx-activity-badge <?php echo e($isRejected ? 'delete' : 'create'); ?>"><?php echo e($isRejected ? 'Ditolak' : 'Disetujui'); ?></span>
                                            <span class="dbx-activity-module"><?php echo e($procurement->category?->name ?? 'Tanpa kategori'); ?></span>
                                        </div>
                                        <span><?php echo e(optional($isRejected ? $procurement->rejected_at : $procurement->approved_at)?->diffForHumans() ?? $procurement->updated_at->diffForHumans()); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-sm text-slate2-400">Belum ada hasil pengadaan buku yang diproses.</p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<?php if($isBorrowerDashboard): ?>
    <div id="borrowDrawerMask" class="dbx-drawer-mask"></div>

    <aside id="borrowDrawer" class="dbx-drawer" aria-hidden="true">
        <div class="dbx-drawer-head">
            <div>
                <div class="dbx-drawer-title">Ajukan Pinjam</div>
                <div class="dbx-drawer-sub">Pilih buku dari daftar. Detail buku dan form pengajuan akan tampil di panel samping ini.</div>
            </div>
            <button type="button" class="dbx-drawer-close" id="borrowDrawerClose">X</button>
        </div>
        <div class="dbx-drawer-body">
            <div id="borrowDrawerEmpty" class="dbx-empty-drawer">
                Klik salah satu buku untuk mulai ajukan pinjam.
            </div>

            <div id="borrowDrawerContent" style="display:none;">
                <div class="dbx-drawer-book">
                    <div class="dbx-drawer-thumb" id="borrowDrawerThumb">
                        <div class="dbx-drawer-fallback" id="borrowDrawerFallback">B</div>
                        <img id="borrowDrawerImage" src="" alt="" style="display:none;">
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div class="dbx-book-chip" id="borrowDrawerStatus">Tersedia</div>
                        <div class="dbx-drawer-book-title" id="borrowDrawerTitle">Judul buku</div>
                        <div class="dbx-drawer-book-author" id="borrowDrawerAuthor">Penulis</div>
                        <div class="dbx-drawer-book-meta">
                            <div class="dbx-drawer-book-box">
                                <div class="dbx-drawer-book-label">Stok</div>
                                <div class="dbx-drawer-book-value" id="borrowDrawerStock">0</div>
                            </div>
                            <div class="dbx-drawer-book-box">
                                <div class="dbx-drawer-book-label">Kategori</div>
                                <div class="dbx-drawer-book-value" id="borrowDrawerCategory">Tanpa kategori</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dbx-drawer-alert">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>PENTING: Batas waktu peminjaman buku ini adalah <strong>1 hari</strong> saja.</span>
                </div>

                <form method="POST" action="<?php echo e(route('loan-requests.store')); ?>" class="dbx-book-form" id="borrowerLoanForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="book_id" id="borrowDrawerBookId">
                    <div class="dbx-book-form-grid">
                        <input type="date" name="borrowed_at" id="borrowDrawerBorrowedAt" class="dbx-book-filter-field" value="<?php echo e(now()->toDateString()); ?>" required>
                        <input type="date" name="due_at" id="borrowDrawerDueAt" class="dbx-book-filter-field" value="<?php echo e(now()->addDay()->toDateString()); ?>" required readonly>
                    </div>
                    <textarea name="notes" class="dbx-book-note" placeholder="Catatan untuk petugas, misalnya ingin ambil langsung di perpustakaan."></textarea>
                    <button type="submit" class="dbx-book-submit" id="borrowDrawerSubmit">
                        <i data-lucide="book-plus" class="w-4 h-4"></i>
                        <span id="borrowDrawerSubmitLabel">Ajukan Pinjam Lewat Sistem</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const borrowDrawer = document.getElementById('borrowDrawer');
        const borrowDrawerMask = document.getElementById('borrowDrawerMask');
        function getBorrowerNotifList() {
            return document.getElementById('borrowerNotificationList');
        }

        function getSanctionAlert() {
            return document.getElementById('borrowerSanctionAlert');
        }

        function getAccountStatus() {
            return document.getElementById('borrowerAccountStatus');
        }

        function getBorrowerBookGrid() {
            return document.getElementById('borrowerBookGrid');
        }

        function getBorrowerBookKeyword() {
            return document.getElementById('borrowerBookKeyword');
        }

        function getBorrowerBookCategoryFilter() {
            return document.getElementById('borrowerBookCategoryFilter');
        }

        function getBorrowerBookAvailabilityFilter() {
            return document.getElementById('borrowerBookAvailabilityFilter');
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getBorrowStateLabel(borrowState) {
            if (borrowState === 'requested') return 'Menunggu petugas';
            if (borrowState === 'borrowed') return 'Sedang dipinjam';
            if (borrowState === 'sanctioned') return 'Akun disanksi';
            if (borrowState === 'available') return 'Tersedia';
            return 'Habis';
        }

        function getBorrowDrawerLabel(borrowState, isAvailable) {
            if (borrowState === 'sanctioned') return 'Pinjam Dinonaktifkan Sementara';
            if (borrowState === 'requested') return 'Pengajuan Sudah Dikirim';
            if (borrowState === 'borrowed') return 'Buku Sedang Dipinjam';
            return isAvailable ? 'Ajukan Pinjam Lewat Sistem' : 'Stok Tidak Tersedia';
        }

        function setBorrowerRealtimePause(isPaused) {
            window.__suspendGlobalPolling = isPaused;
        }

        function showToast(notification) {
            const toastWrap = document.getElementById('borrowerToastWrap');
            if (!toastWrap || !notification) return;

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

        function bindBorrowBookCards() {
            document.querySelectorAll('.js-borrow-book').forEach(function (button) {
                if (button.dataset.bound === '1') {
                    return;
                }

                button.dataset.bound = '1';
                button.addEventListener('click', function () {
                    openBorrowDrawer(button);
                    if (typeof showToast === 'function') {
                        showToast({
                            title: 'Info Batas Waktu',
                            body: 'Peminjaman buku ini dibatasi maksimal 1 hari.',
                            tone: 'info'
                        });
                    }
                });

                button.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openBorrowDrawer(button);
                    }
                });
            });
        }

        function applyBorrowerLoanRequestOptimistic(bookId) {
            if (!bookId) {
                return;
            }

            const card = document.querySelector('.js-borrow-book[data-id="' + CSS.escape(String(bookId)) + '"]');

            if (!card) {
                return;
            }

            card.dataset.borrowState = 'requested';
            card.dataset.canBorrow = '0';

            const chip = card.querySelector('.dbx-book-chip');
            if (chip) {
                chip.textContent = 'Menunggu petugas';
                chip.classList.add('unavailable');
            }

            const requestedStat = document.getElementById('stat-requested');
            if (requestedStat) {
                const currentValue = Number(requestedStat.textContent || 0);
                requestedStat.textContent = String(currentValue + 1);
            }
        }

        function renderBorrowerBooks(books) {
            const borrowerBookGrid = getBorrowerBookGrid();

            if (!borrowerBookGrid) {
                return;
            }

            if (!Array.isArray(books) || books.length === 0) {
                borrowerBookGrid.innerHTML = '<div class="text-sm text-slate2-400" id="borrowerBookEmpty">Buku yang dicari belum ditemukan.</div>';
                return;
            }

            borrowerBookGrid.innerHTML = books.map(function (book) {
                const chipClass = book.borrow_state === 'available' ? '' : ' unavailable';
                const chipLabel = getBorrowStateLabel(book.borrow_state);
                const imageHtml = book.cover_url
                    ? '<img src="' + escapeHtml(book.cover_url) + '" alt="' + escapeHtml(book.title) + '">'
                    : '<div class="dbx-book-fallback">' + escapeHtml((book.title || 'B').trim().charAt(0).toUpperCase()) + '</div>';

                return '<article class="dbx-book-card js-borrow-book"'
                    + ' role="button" tabindex="0"'
                    + ' data-id="' + escapeHtml(book.id) + '"'
                    + ' data-title="' + escapeHtml(book.title) + '"'
                    + ' data-author="' + escapeHtml(book.author || 'Penulis tidak tersedia') + '"'
                    + ' data-category="' + escapeHtml(book.category || 'Tanpa kategori') + '"'
                    + ' data-stock="' + escapeHtml(book.stock) + '"'
                    + ' data-cover-url="' + escapeHtml(book.cover_url || '') + '"'
                    + ' data-borrowed-at="' + escapeHtml(book.borrowed_at) + '"'
                    + ' data-due-at="' + escapeHtml(book.due_at) + '"'
                    + ' data-borrow-state="' + escapeHtml(book.borrow_state) + '"'
                    + ' data-can-borrow="' + (book.can_borrow ? '1' : '0') + '">'
                    + '<div class="dbx-book-thumb">' + imageHtml + '</div>'
                    + '<div class="dbx-book-body">'
                    + '<div class="dbx-book-chip' + chipClass + '">' + chipLabel + '</div>'
                    + '<div class="dbx-book-name">' + escapeHtml(book.title) + '</div>'
                    + '<div class="dbx-book-author">' + escapeHtml(book.author || 'Penulis tidak tersedia') + '</div>'
                    + '<div class="dbx-book-meta"><span class="dbx-book-stock">' + escapeHtml(book.stock) + ' stok</span><span>' + escapeHtml(book.category || 'Umum') + '</span></div>'
                    + '<div class="dbx-book-open"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>Lihat</div>'
                    + '</div></article>';
            }).join('');

            if (window.lucide) {
                window.lucide.createIcons();
            }

            bindBorrowBookCards();
        }

        function getBorrowerImageSearchCameraInput() {
            return document.getElementById('borrowerImageSearchCameraInput');
        }

        function getBorrowerImageSearchGalleryInput() {
            return document.getElementById('borrowerImageSearchGalleryInput');
        }

        function getBorrowerImageSearchPreviewWrap() {
            return document.getElementById('borrowerImageSearchPreviewWrap');
        }

        function getBorrowerImageSearchPreview() {
            return document.getElementById('borrowerImageSearchPreview');
        }

        function getBorrowerImageSearchFileName() {
            return document.getElementById('borrowerImageSearchFileName');
        }

        function getBorrowerImageSearchStatus() {
            return document.getElementById('borrowerImageSearchStatus');
        }

        function getBorrowerImageSearchResults() {
            return document.getElementById('borrowerImageSearchResults');
        }

        function setBorrowerImageSearchPreview(file) {
            const previewWrap = getBorrowerImageSearchPreviewWrap();
            const preview = getBorrowerImageSearchPreview();
            const fileName = getBorrowerImageSearchFileName();
            const actions = document.getElementById('borrowerImageSearchActions');

            if (!previewWrap || !preview || !fileName) {
                return;
            }

            if (!file) {
                previewWrap.style.display = 'none';
                preview.removeAttribute('src');
                fileName.style.display = 'none';
                fileName.textContent = '';
                if (actions) actions.style.display = 'none';
                return;
            }

            preview.src = URL.createObjectURL(file);
            previewWrap.style.display = 'flex';
            fileName.style.display = 'block';
            fileName.textContent = file.name || 'foto-buku';
            if (actions) actions.style.display = 'flex';
        }

        function renderBorrowerImageSearchResults(books, message) {
            const container = getBorrowerImageSearchResults();

            if (!container) {
                return;
            }

            if (!Array.isArray(books) || books.length === 0) {
                container.style.display = 'block';
                container.innerHTML = '<div class="text-sm text-slate2-400">Buku tidak ditemukan.</div>';
                return;
            }

            container.style.display = 'block';
            container.innerHTML = '<div class="dbx-book-section-sub" style="margin-bottom:12px;">' + escapeHtml(message || 'Buku hasil pencarian foto') + '</div>'
                + '<div class="dbx-book-grid">' + books.map(function (book) {
                    const chipClass = book.borrow_state === 'available' ? '' : ' unavailable';
                    const chipLabel = getBorrowStateLabel(book.borrow_state);
                    const imageHtml = book.cover_url
                        ? '<img src="' + escapeHtml(book.cover_url) + '" alt="' + escapeHtml(book.title) + '">'
                        : '<div class="dbx-book-fallback">' + escapeHtml((book.title || 'B').trim().charAt(0).toUpperCase()) + '</div>';

                    return '<article class="dbx-book-card js-borrow-book"'
                        + ' role="button" tabindex="0"'
                        + ' data-id="' + escapeHtml(book.id) + '"'
                        + ' data-title="' + escapeHtml(book.title) + '"'
                        + ' data-author="' + escapeHtml(book.author || 'Penulis tidak tersedia') + '"'
                        + ' data-category="' + escapeHtml(book.category_name || book.category || 'Tanpa kategori') + '"'
                        + ' data-stock="' + escapeHtml(book.stock) + '"'
                        + ' data-cover-url="' + escapeHtml(book.cover_url || '') + '"'
                        + ' data-borrowed-at="' + escapeHtml(book.borrowed_at || '') + '"'
                        + ' data-due-at="' + escapeHtml(book.due_at || '') + '"'
                        + ' data-borrow-state="' + escapeHtml(book.borrow_state || 'available') + '"'
                        + ' data-can-borrow="' + (book.can_borrow ? '1' : '0') + '">'
                        + '<div class="dbx-book-thumb">' + imageHtml + '</div>'
                        + '<div class="dbx-book-body">'
                        + '<div class="dbx-book-chip' + chipClass + '">' + chipLabel + '</div>'
                        + '<div class="dbx-book-name">' + escapeHtml(book.title) + '</div>'
                        + '<div class="dbx-book-author">' + escapeHtml(book.author || 'Penulis tidak tersedia') + '</div>'
                        + '<div class="dbx-book-meta"><span class="dbx-book-stock">' + escapeHtml(book.stock) + ' stok</span><span>' + escapeHtml(book.category_name || book.category || 'Umum') + '</span></div>'
                        + '<div class="dbx-book-open"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>Lihat</div>'
                        + '</div></article>';
                }).join('') + '</div>';

            if (window.lucide) {
                window.lucide.createIcons();
            }

            bindBorrowBookCards();
        }

        async function searchBorrowerBooksByImage(file) {
            const status = getBorrowerImageSearchStatus();
            const results = getBorrowerImageSearchResults();

            if (!file || !status || !results) {
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '<?php echo e(csrf_token()); ?>');
            formData.append('image', file);

            status.textContent = 'Mencari dari foto...';
            results.style.display = 'block';
            results.innerHTML = '<div class="text-sm text-slate2-400">Sedang mencari buku...</div>';

            try {
                const response = await fetch('<?php echo e(route('admin.books.search-by-image')); ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Pencarian foto gagal.');
                }

                const books = Array.isArray(data.books) ? data.books : [];
                if (books.length === 0) {
                    status.textContent = '';
                    results.innerHTML = '<div class="text-sm text-slate2-400">Buku tidak ditemukan.</div>';
                    return;
                }

                status.textContent = data.message || 'Pencarian selesai.';
                renderBorrowerImageSearchResults(books, data.message || 'Hasil pencarian foto');
            } catch (error) {
                status.textContent = error.message || 'Pencarian foto gagal.';
                results.innerHTML = '<div class="text-sm text-slate2-400">Gagal memproses foto. Coba foto yang lebih jelas.</div>';
            }
        }

        function syncBorrowerBookCategories(categories, selectedValue) {
            const borrowerBookCategoryFilter = getBorrowerBookCategoryFilter();

            if (!borrowerBookCategoryFilter || !Array.isArray(categories)) {
                return;
            }

            borrowerBookCategoryFilter.innerHTML = '<option value="">Semua kategori</option>' + categories.map(function (category) {
                const selected = selectedValue === category.slug ? ' selected' : '';
                return '<option value="' + escapeHtml(category.slug) + '"' + selected + '>' + escapeHtml(category.name) + '</option>';
            }).join('');
        }

        async function refreshBorrowerBooks() {
            const borrowerBookGrid = getBorrowerBookGrid();
            const borrowerBookKeyword = getBorrowerBookKeyword();
            const borrowerBookCategoryFilter = getBorrowerBookCategoryFilter();
            const borrowerBookAvailabilityFilter = getBorrowerBookAvailabilityFilter();

            if (!borrowerBookGrid || !borrowerBookKeyword || !borrowerBookCategoryFilter || !borrowerBookAvailabilityFilter) {
                return;
            }

            const activeDrawerBookId = borrowDrawer?.classList.contains('open')
                ? (document.getElementById('borrowDrawerBookId')?.value || '')
                : '';
            const params = new URLSearchParams({
                q: (borrowerBookKeyword.value || '').trim(),
                category: borrowerBookCategoryFilter.value || '',
                availability: borrowerBookAvailabilityFilter.value || 'available'
            });

            try {
                const response = await fetch('<?php echo e(route('borrower.books')); ?>?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                syncBorrowerBookCategories(Array.isArray(data.categories) ? data.categories : [], params.get('category') || '');
                renderBorrowerBooks(Array.isArray(data.books) ? data.books : []);

                if (activeDrawerBookId && borrowDrawer?.classList.contains('open')) {
                    const nextBookCard = borrowerBookGrid.querySelector('[data-id="' + CSS.escape(activeDrawerBookId) + '"]');
                    if (nextBookCard) {
                        openBorrowDrawer(nextBookCard);
                    }
                }
            } catch (error) {
                console.error('Error fetching borrower books:', error);
            }
        }

        let borrowerSearchTimeout;

        document.addEventListener('input', function (event) {
            if (event.target?.id !== 'borrowerBookKeyword') {
                return;
            }

            clearTimeout(borrowerSearchTimeout);
            borrowerSearchTimeout = setTimeout(refreshBorrowerBooks, 300);
        });

        document.addEventListener('change', function (event) {
            if (event.target?.id === 'borrowerBookCategoryFilter' || event.target?.id === 'borrowerBookAvailabilityFilter') {
                refreshBorrowerBooks();
            }
        });

        document.addEventListener('submit', function (event) {
            const borrowerBookFilterForm = event.target.closest('#borrowerBookFilterForm');

            if (!borrowerBookFilterForm) {
                return;
            }

            event.preventDefault();

            const file = borrowerImageSearchCameraInput && borrowerImageSearchCameraInput.files && borrowerImageSearchCameraInput.files[0]
                ? borrowerImageSearchCameraInput.files[0]
                : (borrowerImageSearchGalleryInput && borrowerImageSearchGalleryInput.files && borrowerImageSearchGalleryInput.files[0]
                    ? borrowerImageSearchGalleryInput.files[0]
                    : null);

            if (file) {
                searchBorrowerBooksByImage(file);
                return;
            }

            refreshBorrowerBooks();
        });

        const borrowerImageSearchCameraInput = getBorrowerImageSearchCameraInput();
        const borrowerImageSearchGalleryInput = getBorrowerImageSearchGalleryInput();
        const borrowerImageSearchSourceBtn = document.getElementById('borrowerImageSearchSourceBtn');
        const borrowerImageSearchSourceMenu = document.getElementById('borrowerImageSearchSourceMenu');
        const borrowerImageSearchCamera = document.getElementById('borrowerImageSearchCamera');
        const borrowerImageSearchGallery = document.getElementById('borrowerImageSearchGallery');
        const borrowerImageSearchActionBtn = document.getElementById('borrowerImageSearchActionBtn');
        const borrowerImageSearchClearBtn = document.getElementById('borrowerImageSearchClearBtn');

        let borrowerCameraStream = null;
        const borrowerCameraModal = document.getElementById('borrowerCameraModal');
        const borrowerCameraVideo = document.getElementById('borrowerCameraVideo');
        const borrowerCameraCanvas = document.getElementById('borrowerCameraCanvas');
        const borrowerCameraCloseBtn = document.getElementById('borrowerCameraCloseBtn');
        const borrowerCameraCaptureBtn = document.getElementById('borrowerCameraCaptureBtn');

        async function openBorrowerCamera() {
            if (!borrowerCameraModal || !borrowerCameraVideo) return;
            borrowerCameraModal.style.display = 'flex';
            try {
                borrowerCameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "environment" }
                });
                borrowerCameraVideo.srcObject = borrowerCameraStream;
            } catch (err) {
                alert("Tidak dapat mengakses kamera: " + err.message);
                closeBorrowerCamera();
            }
        }

        function closeBorrowerCamera() {
            if (!borrowerCameraModal) return;
            borrowerCameraModal.style.display = 'none';
            if (borrowerCameraStream) {
                borrowerCameraStream.getTracks().forEach(track => track.stop());
                borrowerCameraStream = null;
            }
            if (borrowerCameraVideo) borrowerCameraVideo.srcObject = null;
        }

        if (borrowerCameraCloseBtn) {
            borrowerCameraCloseBtn.addEventListener('click', closeBorrowerCamera);
        }

        if (borrowerCameraCaptureBtn) {
            borrowerCameraCaptureBtn.addEventListener('click', function() {
                if (!borrowerCameraStream || !borrowerCameraVideo || !borrowerCameraCanvas) return;
                
                borrowerCameraCanvas.width = borrowerCameraVideo.videoWidth;
                borrowerCameraCanvas.height = borrowerCameraVideo.videoHeight;
                const ctx = borrowerCameraCanvas.getContext('2d');
                ctx.drawImage(borrowerCameraVideo, 0, 0, borrowerCameraCanvas.width, borrowerCameraCanvas.height);
                
                borrowerCameraCanvas.toBlob(function(blob) {
                    if (blob) {
                        const file = new File([blob], "camera-photo.jpg", { type: "image/jpeg" });
                        
                        if (borrowerImageSearchCameraInput) borrowerImageSearchCameraInput.value = '';
                        if (borrowerImageSearchGalleryInput) borrowerImageSearchGalleryInput.value = '';
                        
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        if (borrowerImageSearchCameraInput) {
                            borrowerImageSearchCameraInput.files = dataTransfer.files;
                        }
                        
                        setBorrowerImageSearchPreview(file);
                    }
                    closeBorrowerCamera();
                }, 'image/jpeg', 0.8);
            });
        }

        if (borrowerImageSearchActionBtn) {
            borrowerImageSearchActionBtn.addEventListener('click', function () {
                const file = borrowerImageSearchCameraInput && borrowerImageSearchCameraInput.files && borrowerImageSearchCameraInput.files[0]
                    ? borrowerImageSearchCameraInput.files[0]
                    : (borrowerImageSearchGalleryInput && borrowerImageSearchGalleryInput.files && borrowerImageSearchGalleryInput.files[0]
                        ? borrowerImageSearchGalleryInput.files[0]
                        : null);
                if (file) {
                    searchBorrowerBooksByImage(file);
                }
            });
        }

        if (borrowerImageSearchClearBtn) {
            borrowerImageSearchClearBtn.addEventListener('click', function () {
                if (borrowerImageSearchCameraInput) borrowerImageSearchCameraInput.value = '';
                if (borrowerImageSearchGalleryInput) borrowerImageSearchGalleryInput.value = '';
                setBorrowerImageSearchPreview(null);
                const results = getBorrowerImageSearchResults();
                const status = getBorrowerImageSearchStatus();
                if (results) results.style.display = 'none';
                if (status) status.textContent = '';
            });
        }

        if (borrowerImageSearchCameraInput) {
            borrowerImageSearchCameraInput.addEventListener('change', function () {
                const file = borrowerImageSearchCameraInput.files && borrowerImageSearchCameraInput.files[0] ? borrowerImageSearchCameraInput.files[0] : null;
                if (borrowerImageSearchGalleryInput) {
                    borrowerImageSearchGalleryInput.value = '';
                }
                setBorrowerImageSearchPreview(file);
            });
        }

        if (borrowerImageSearchGalleryInput) {
            borrowerImageSearchGalleryInput.addEventListener('change', function () {
                const file = borrowerImageSearchGalleryInput.files && borrowerImageSearchGalleryInput.files[0] ? borrowerImageSearchGalleryInput.files[0] : null;
                if (borrowerImageSearchCameraInput) {
                    borrowerImageSearchCameraInput.value = '';
                }
                setBorrowerImageSearchPreview(file);
            });
        }

        if (borrowerImageSearchCamera) {
            borrowerImageSearchCamera.addEventListener('click', function () {
                openBorrowerCamera();
                if (borrowerImageSearchSourceMenu) {
                    borrowerImageSearchSourceMenu.style.display = 'none';
                }
            });
        }

        if (borrowerImageSearchGallery) {
            borrowerImageSearchGallery.addEventListener('click', function () {
                if (borrowerImageSearchGalleryInput) {
                    borrowerImageSearchGalleryInput.click();
                }
                if (borrowerImageSearchSourceMenu) {
                    borrowerImageSearchSourceMenu.style.display = 'none';
                }
            });
        }

        if (borrowerImageSearchSourceBtn && borrowerImageSearchSourceMenu) {
            borrowerImageSearchSourceBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                borrowerImageSearchSourceMenu.style.display = borrowerImageSearchSourceMenu.style.display === 'block' ? 'none' : 'block';
            });
        }

        document.addEventListener('click', function (event) {
            if (!borrowerImageSearchSourceMenu) {
                return;
            }

            const clickedInside = event.target.closest('#borrowerImageSearchSourceMenu') || event.target.closest('#borrowerImageSearchSourceBtn');
            if (!clickedInside) {
                borrowerImageSearchSourceMenu.style.display = 'none';
            }
        });

        document.addEventListener('notificationsUpdated', function (event) {
            const data = event.detail;
            const notifications = Array.isArray(data.notifications) ? data.notifications : [];
            const dashboardWrap = document.getElementById('asyncDashboardWrap');
            const borrowerNotifList = getBorrowerNotifList();
            const sanctionAlert = getSanctionAlert();
            const accountStatus = getAccountStatus();

            if (dashboardWrap?.dataset.dashboardRole === 'principal') {
                const nextSignatures = notifications
                    .map(function (notification) {
                        return notification.signature || '';
                    })
                    .filter(Boolean)
                    .join('|');

                if (dashboardWrap.dataset.principalSignatures !== nextSignatures) {
                    dashboardWrap.dataset.principalSignatures = nextSignatures;
                    refreshAsyncTargets(['#asyncDashboardWrap']).catch(function (error) {
                        console.error('Error refreshing principal dashboard:', error);
                    });
                }

                return;
            }

            if (dashboardWrap?.dataset.dashboardRole === 'borrower') {
                const nextSignatures = notifications
                    .map(function (notification) {
                        return notification.signature || '';
                    })
                    .filter(Boolean)
                    .join('|');
                const nextStateSignature = data.signature || nextSignatures;
                const stateChanged = dashboardWrap.dataset.borrowerStateSignature !== nextStateSignature;
                const notificationChanged = dashboardWrap.dataset.borrowerSignatures !== nextSignatures;

                if (stateChanged || notificationChanged) {
                    dashboardWrap.dataset.borrowerSignatures = nextSignatures;
                    dashboardWrap.dataset.borrowerStateSignature = nextStateSignature;
                    refreshBorrowerBooks().catch(function (error) {
                        console.error('Error refreshing borrower books:', error);
                    });
                }
            }

            // Update dashboard notif list
            if (borrowerNotifList) {
                if (notifications.length === 0) {
                    borrowerNotifList.innerHTML = '';
                    borrowerNotifList.style.display = 'none';
                } else {
                    borrowerNotifList.style.display = 'grid';
                    borrowerNotifList.innerHTML = notifications.map(function (n) {
                        return '<article class="dbx-notif-item ' + (n.tone || 'info') + '">'
                            + '<div class="dbx-notif-title">' + (n.title || 'Notifikasi') + '</div>'
                            + '<div class="dbx-notif-body">' + (n.body || '') + '</div>'
                            + '</article>';
                    }).join('');
                }
            }

            // Update sanction alert
            if (sanctionAlert) {
                if (data.sanction_message) {
                    sanctionAlert.style.display = 'block';
                    sanctionAlert.textContent = data.sanction_message;
                } else {
                    sanctionAlert.style.display = 'none';
                }
            }

            // Update account status
            if (accountStatus && data.account_status) {
                accountStatus.innerHTML = 'Status akun: <strong>' + escapeHtml(data.account_status) + '</strong>';
            }

            // Update loan stats
            if (data.borrower_loan_stats) {
                const reqEl = document.getElementById('stat-requested');
                const borEl = document.getElementById('stat-borrowed');
                const retEl = document.getElementById('stat-returned');
                
                if (reqEl) reqEl.textContent = data.borrower_loan_stats.requested || 0;
                if (borEl) borEl.textContent = data.borrower_loan_stats.borrowed || 0;
                if (retEl) retEl.textContent = data.borrower_loan_stats.returned || 0;
            }
        });

        // Make closeBorrowDrawer globally accessible for data-success-call
        window.closeBorrowDrawer = function() {
            const borrowDrawer = document.getElementById('borrowDrawer');
            const borrowDrawerMask = document.getElementById('borrowDrawerMask');
            if (!borrowDrawer || !borrowDrawerMask) {
                return;
            }

            borrowDrawer.classList.remove('open');
            borrowDrawerMask.classList.remove('show');
            borrowDrawer.setAttribute('aria-hidden', 'true');
            setBorrowerRealtimePause(false);
        };

        function syncBorrowDrawerDueAt() {
            const borrowedAt = document.getElementById('borrowDrawerBorrowedAt');
            const dueAt = document.getElementById('borrowDrawerDueAt');

            if (!borrowedAt || !dueAt || !borrowedAt.value) {
                return;
            }

            const borrowedDate = new Date(borrowedAt.value + 'T00:00:00');
            borrowedDate.setDate(borrowedDate.getDate() + 1);
            dueAt.value = borrowedDate.toISOString().slice(0, 10);
        }

        function openBorrowDrawer(button) {
            if (!borrowDrawer || !borrowDrawerMask || !button) {
                return;
            }

            const content = document.getElementById('borrowDrawerContent');
            const empty = document.getElementById('borrowDrawerEmpty');
            const image = document.getElementById('borrowDrawerImage');
            const fallback = document.getElementById('borrowDrawerFallback');
            const status = document.getElementById('borrowDrawerStatus');
            const title = document.getElementById('borrowDrawerTitle');
            const author = document.getElementById('borrowDrawerAuthor');
            const stock = document.getElementById('borrowDrawerStock');
            const category = document.getElementById('borrowDrawerCategory');
            const bookId = document.getElementById('borrowDrawerBookId');
            const borrowedAt = document.getElementById('borrowDrawerBorrowedAt');
            const submit = document.getElementById('borrowDrawerSubmit');
            const submitLabel = document.getElementById('borrowDrawerSubmitLabel');
            const borrowState = button.dataset.borrowState || 'unavailable';
            const isAvailable = borrowState === 'available';
            const bookTitle = button.dataset.title || 'Judul buku';

            empty.style.display = 'none';
            content.style.display = 'block';

            title.textContent = bookTitle;
            author.textContent = button.dataset.author || 'Penulis tidak tersedia';
            stock.textContent = 'Stok ' + (button.dataset.stock || '0');
            category.textContent = button.dataset.category || 'Tanpa kategori';
            bookId.value = button.dataset.id || '';
            borrowedAt.value = button.dataset.borrowedAt || '';
            syncBorrowDrawerDueAt();
            fallback.textContent = (bookTitle.trim().charAt(0) || 'B').toUpperCase();

            status.textContent = getBorrowStateLabel(borrowState) === 'Habis'
                ? 'Tidak tersedia'
                : getBorrowStateLabel(borrowState);
            status.classList.toggle('unavailable', !isAvailable);
            submit.disabled = !isAvailable;
            submitLabel.textContent = getBorrowDrawerLabel(borrowState, isAvailable);

            if (button.dataset.coverUrl) {
                image.src = button.dataset.coverUrl;
                image.alt = bookTitle;
                image.style.display = 'block';
                fallback.style.display = 'none';
            } else {
                image.src = '';
                image.alt = '';
                image.style.display = 'none';
                fallback.style.display = 'flex';
            }

            borrowDrawer.classList.add('open');
            borrowDrawerMask.classList.add('show');
            borrowDrawer.setAttribute('aria-hidden', 'false');
            setBorrowerRealtimePause(true);
        }

        bindBorrowBookCards();

        if (borrowDrawerMask) {
            borrowDrawerMask.addEventListener('click', closeBorrowDrawer);
        }

        const borrowDrawerClose = document.getElementById('borrowDrawerClose');
        if (borrowDrawerClose) {
            borrowDrawerClose.addEventListener('click', closeBorrowDrawer);
        }

        const borrowDrawerBorrowedAt = document.getElementById('borrowDrawerBorrowedAt');
        if (borrowDrawerBorrowedAt) {
            borrowDrawerBorrowedAt.addEventListener('change', syncBorrowDrawerDueAt);
            syncBorrowDrawerDueAt();
        }

        let borrowerLoanSubmitBusy = false;

        const borrowerLoanForm = document.getElementById('borrowerLoanForm');
        if (borrowerLoanForm) {
            borrowerLoanForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const submitBtn = document.getElementById('borrowDrawerSubmit');
                const submitLabel = document.getElementById('borrowDrawerSubmitLabel');
                const bookId = document.getElementById('borrowDrawerBookId')?.value || '';
                const originalLabel = submitLabel.textContent;
                
                // Disable button and show loading
                borrowerLoanSubmitBusy = true;
                setBorrowerRealtimePause(true);
                submitBtn.disabled = true;
                submitLabel.textContent = 'Sedang mengirim...';

                const formData = new FormData(borrowerLoanForm);

                fetch(borrowerLoanForm.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(async function (response) {
                    const rawBody = await response.text();
                    let result = {};

                    try {
                        result = rawBody ? JSON.parse(rawBody) : {};
                    } catch (parseError) {
                        result = { message: rawBody || 'Terjadi kesalahan saat mengirim pengajuan.' };
                    }

                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || ('Terjadi kesalahan saat mengirim pengajuan. Status: ' + response.status));
                    }

                    return result;
                })
                    .then(function (result) {
                        applyBorrowerLoanRequestOptimistic(bookId);
                        borrowerLoanForm.reset();
                        showToast({
                            title: 'Berhasil!',
                            body: result.message || 'Pengajuan berhasil dikirim.',
                            tone: 'success'
                        });

                        closeBorrowDrawer();
                        submitLabel.textContent = originalLabel;
                        borrowerLoanSubmitBusy = false;
                        submitBtn.disabled = false;
                        setBorrowerRealtimePause(false);

                        Promise.allSettled([
                            refreshGlobalNotifications(false),
                            refreshBorrowerBooks(),
                        ]);
                    })
                    .catch(function (error) {
                        showToast({
                            title: 'Gagal',
                            body: error.message || 'Terjadi kesalahan saat mengirim pengajuan.',
                            tone: 'danger'
                        });

                        borrowerLoanSubmitBusy = false;
                        submitBtn.disabled = false;
                        setBorrowerRealtimePause(false);
                        submitLabel.textContent = originalLabel;
                        refreshBorrowerBooks().catch(function (refreshError) {
                            console.error('Error restoring borrower books:', refreshError);
                        });
                    });
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeBorrowDrawer();
            }
        });

        let borrowerRealtimeBusy = false;

        async function syncBorrowerRealtime() {
            const dashboardWrap = document.getElementById('asyncDashboardWrap');
            const borrowerBookGrid = getBorrowerBookGrid();

            if (!borrowerBookGrid || borrowerRealtimeBusy || borrowerLoanSubmitBusy || document.hidden || dashboardWrap?.dataset.dashboardRole !== 'borrower') {
                return;
            }

            borrowerRealtimeBusy = true;

            try {
                await Promise.all([
                    refreshGlobalNotifications(false),
                    refreshBorrowerBooks(),
                ]);
            } finally {
                borrowerRealtimeBusy = false;
            }
        }

        if (getBorrowerBookGrid()) {
            syncBorrowerRealtime();
            window.setInterval(function () {
                syncBorrowerRealtime();
            }, 10000);

            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    syncBorrowerRealtime();
                }
            });
        }

        // Auto refresh entire dashboard for stats/logs
        window.setInterval(function() {
            const dashboardWrap = document.querySelector('#asyncDashboardWrap');
            if (dashboardWrap && dashboardWrap.dataset.dashboardRole !== 'borrower') {
                refreshAsyncTargets(['#asyncDashboardWrap']);
            }
        }, 30000);

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>