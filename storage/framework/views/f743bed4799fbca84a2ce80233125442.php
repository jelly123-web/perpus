<?php ($appName = \App\Models\Setting::valueOr('app_name', config('app.name', 'Laravel'))); ?>
<?php ($appColor = \App\Models\Setting::valueOr('app_color', '#FAFAFA')); ?>

<?php $__env->startSection('content'); ?>
<?php ($title = 'Laporan'); ?>
<?php ($eyebrow = 'Analitik & Rekapitulasi'); ?>

<style>
    .report-page{display:flex;flex-direction:column;gap:32px;padding-bottom:80px;width:100%}
    .report-toolbar{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;padding-bottom:24px;border-bottom:1px solid #e2e8f0}
    .report-title-wrap{display:flex;align-items:flex-start;gap:16px}
    .report-title-copy{display:flex;flex-direction:column;gap:8px}
    .report-title{font-family:'Playfair Display',serif;font-size:44px;font-weight:900;letter-spacing:-.04em;color:var(--fg);margin:0;line-height:1}
    .report-title-accent{color:var(--accent)}
    .report-subtitle{font-size:14px;color:var(--muted);font-weight:500}
    .report-actions{display:flex;gap:10px;flex-wrap:wrap}
    .btn-report-action,.btn-apply{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 18px;border-radius:10px;background:#fff;border:1.5px solid var(--border-light);color:var(--fg);font-size:13px;font-weight:600;cursor:pointer;transition:all .25s ease;box-shadow:var(--shadow-sm);text-decoration:none;height:42px;font-family:'Inter','Segoe UI',sans-serif}
    .btn-report-action:hover,.btn-apply:hover{border-color:var(--accent);color:var(--accent);transform:translateY(-1px);box-shadow:var(--shadow-md)}
    .btn-report-action.primary{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 8px 24px var(--accent-glow)}
    .btn-report-action.primary:hover{background:var(--accent-light);color:#fff;border-color:var(--accent-light);box-shadow:0 8px 24px var(--accent-glow)}

    .report-tabs{display:flex;gap:4px;padding:4px;background:var(--bg-soft);border-radius:12px;width:fit-content;border:1px solid var(--border-light)}
    .report-tab{padding:10px 24px;border-radius:8px;font-size:13px;font-weight:600;color:var(--muted);text-decoration:none;transition:all .25s ease;cursor:pointer;border:none;background:transparent;font-family:inherit}
    .report-tab:hover{color:var(--accent);background:rgba(255,255,255,.65)}
    .report-tab.active{background:#fff;color:var(--accent);font-weight:700;box-shadow:var(--shadow-sm);transform:none}

    .report-filter-card{background:#fff;border:1.5px solid var(--border-light);border-radius:16px;padding:24px 28px;box-shadow:var(--shadow-sm);position:relative;overflow:hidden}
    .report-filter-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent),var(--teal),var(--gold))}
    .report-filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:18px;align-items:flex-end}
    .report-micro{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.1em}

    .report-filter-card .form-select,.report-filter-card .form-input{padding:10px 14px!important;font-size:13px;border-radius:10px!important;border:1.5px solid var(--border-light);background:var(--bg-soft);color:var(--fg);font-family:'Inter','Segoe UI',sans-serif;transition:all .2s ease;outline:none;width:100%}
    .report-filter-card .form-select:focus,.report-filter-card .form-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);background:#fff}

    .report-stats-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}
    .report-stat-card{background:#fff;border:1.5px solid var(--border-light);border-radius:16px;padding:24px;display:flex;flex-direction:column;gap:16px;box-shadow:var(--shadow-sm);transition:all .3s ease;position:relative;overflow:hidden}
    .report-stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;transform:scaleX(0);transition:transform .3s ease;transform-origin:left;background:var(--accent)}
    .report-stat-card.books-card::before{background:var(--accent)}
    .report-stat-card.loans-card::before{background:var(--gold)}
    .report-stat-card.returns-card::before{background:var(--teal)}
    .report-stat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg);border-color:transparent}
    .report-stat-card:hover::before{transform:scaleX(1)}
    .report-stat-header{display:flex;align-items:center;justify-content:space-between}
    .report-stat-icon-box{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px}
    .report-stat-icon-box.books{background:var(--accent-glow);color:var(--accent)}
    .report-stat-icon-box.loans{background:var(--gold-glow);color:var(--gold)}
    .report-stat-icon-box.returns{background:var(--teal-glow);color:var(--teal)}
    .report-stat-content{display:flex;flex-direction:column}
    .report-stat-value{font-family:'Playfair Display',serif;font-size:40px;font-weight:900;color:var(--fg);letter-spacing:-.03em;line-height:1}
    .report-stat-label{font-size:13px;font-weight:600;color:var(--muted);margin-top:4px}
    .report-stat-footer{font-size:11px;color:var(--dim);display:flex;align-items:center;gap:4px;font-weight:600}

    .report-usage-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px}
    .report-usage-widget{background:#fff;border:1.5px solid var(--border-light);border-radius:14px;padding:20px;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;gap:8px;min-height:120px;transition:all .25s ease;position:relative;overflow:hidden}
    .report-usage-widget::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--accent);opacity:0;transition:opacity .25s ease}
    .report-usage-widget:hover{background:var(--bg-soft);transform:translateY(-2px);box-shadow:var(--shadow-md)}
    .report-usage-widget:hover::before{opacity:1}
    .report-usage-tag{font-size:10px;font-weight:700;color:var(--dim);text-transform:uppercase;letter-spacing:.12em}
    .report-usage-number{font-family:'Playfair Display',serif;font-size:24px;font-weight:800;color:var(--fg);line-height:1.2}
    .report-usage-desc{font-size:12px;color:var(--dim);font-weight:500}

    .report-section-card{background:#fff;border:1.5px solid var(--border-light);border-radius:20px;overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:16px;width:100%;transition:box-shadow .3s ease}
    .report-section-card:hover{box-shadow:var(--shadow-md)}
    .report-section-header{padding:24px 28px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;gap:20px;background:linear-gradient(90deg,var(--bg-soft) 0%,#fff 100%);position:relative}
    .report-section-header::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--accent)}
    .report-section-info{display:flex;align-items:flex-start;gap:14px}
    .report-section-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:var(--accent-glow);color:var(--accent);box-shadow:inset 0 0 0 1px rgba(196,149,106,.16)}
    .report-section-copy{display:flex;flex-direction:column;gap:4px}
    .report-section-title-text{font-family:'Playfair Display',serif;font-size:22px;font-weight:800;color:var(--fg)}
    .report-section-subtitle-text{font-size:13px;color:var(--muted);max-width:500px;line-height:1.5}
    .report-section-count{padding:6px 14px;border-radius:8px;background:var(--accent-glow);border:1px solid rgba(196,149,106,.15);color:var(--accent);font-size:12px;font-weight:700;white-space:nowrap;box-shadow:none}

    .report-table{width:100%;border-collapse:collapse;table-layout:fixed}
    .report-table th{background:var(--bg-soft);padding:12px 24px;font-size:10px;font-weight:700;color:var(--dim);text-transform:uppercase;letter-spacing:.12em;border-bottom:1px solid var(--border-light);text-align:left}
    .report-table td{padding:14px 24px;border-bottom:1px solid rgba(215,208,196,.45);font-size:13px;color:var(--muted);vertical-align:middle;word-wrap:break-word;transition:background .15s ease}
    .report-table tbody tr:nth-child(even){background:#fcfcfd}
    .report-table tr:last-child td{border-bottom:none}
    .report-table tr:hover td{background:rgba(196,149,106,.06)}

    .report-book-title{font-weight:700;color:var(--fg)}
    .report-book-author{font-size:11px;color:var(--dim);margin-top:2px}
    .report-category-badge{padding:4px 10px;border-radius:6px;background:rgba(196,149,106,.12);color:var(--accent);font-size:11px;font-weight:700;display:inline-block}
    .report-isbn{font-family:monospace;font-size:11px;color:var(--dim)}
    .report-stock-available{font-size:12px;font-weight:800;color:var(--teal)}
    .report-stock-available.low{color:var(--red)}
    .report-stock-divider,.report-stock-total{font-size:12px;color:var(--dim);margin:0 3px}
    .report-pop-row{display:flex;align-items:center;gap:5px;color:var(--gold)}
    .report-pop-count{font-weight:800;font-size:12px}

    .report-member-row{display:flex;align-items:center;gap:8px;padding-top:8px;border-top:1px dashed #e2e8f0;margin-top:8px}
    .report-member-avatar{width:22px;height:22px;border-radius:50%;background:var(--accent-glow);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800}
    .report-member-name{font-size:11px;font-weight:600;color:var(--muted)}
    .report-date-main{font-weight:700;font-size:12px;color:var(--fg)}
    .report-date-due{font-weight:700;font-size:12px;color:var(--red)}
    .report-date-label{font-size:8px;color:var(--dim);text-transform:uppercase;letter-spacing:.12em;font-weight:600;margin-top:1px}
    .report-staff-badge{font-size:10px;font-weight:600;color:var(--muted);background:var(--bg-soft);padding:4px 8px;border-radius:6px;border:1px solid rgba(215,208,196,.45)}
    .report-pill-badge{padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:none}
    .report-pill-badge.borrowed{background:var(--gold-glow);color:var(--gold)}
    .report-pill-badge.late{background:var(--red-glow);color:var(--red)}
    .report-pill-badge.returned{background:var(--teal-glow);color:var(--teal)}
    .report-return-row{display:flex;align-items:center;gap:6px;color:var(--teal)}
    .report-return-value{font-weight:800;font-size:12px}
    .report-note-late{font-size:11px;color:var(--red);font-weight:600;font-style:italic}
    .report-note-ok{font-size:11px;color:var(--dim);font-style:italic}

    .report-book-info-cell{display:flex;align-items:center;gap:14px}
    .report-book-cover{width:44px;height:60px;border-radius:8px;background:var(--accent-glow);display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:16px;font-weight:800;flex-shrink:0;box-shadow:var(--shadow-sm);overflow:hidden;border:1px solid rgba(196,149,106,.1)}
    .report-book-cover img{width:100%;height:100%;object-fit:cover}

    .report-empty-state{padding:80px 40px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px}
    .report-empty-icon{width:80px;height:80px;background:var(--bg-soft);border-radius:30px;display:flex;align-items:center;justify-content:center;color:var(--dim)}
    .report-empty-text{font-size:16px;font-weight:600;color:var(--muted)}

    .report-print-head{display:none;padding:0 0 18px 0;border-bottom:3px double #333;margin-bottom:20px}
    .report-print-header-content{display:flex;align-items:center;justify-content:center;gap:20px}
    .report-print-logo{width:80px;height:80px;border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
    .report-print-logo img{width:100%;height:100%;object-fit:contain}
    .report-print-logo-fallback{width:100%;height:100%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800}
    .report-print-info{text-align:left}
    .report-print-info h1{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#000;margin:0}
    .report-print-info p{font-size:14px;color:#444;margin:4px 0 0 0}
    .report-print-section-note{display:none}
    .report-print-stats-table{display:none;width:100%;border-collapse:collapse;margin:20px 0;border:1px solid #000}
    .report-print-stats-table th{background:#f0f0f0;padding:12px;border:1px solid #000;text-align:left;font-weight:800;font-size:14px}
    .report-print-stats-table td{padding:10px 12px;border:1px solid #000;font-size:13px}

    @media print {
        @page{size:auto;margin:16mm 12mm}
        body{background:#fff!important;color:#000!important}
        .topbar,.sidebar,.report-actions,.report-filter-card,.report-tabs,.report-toolbar,#chatbotRoot{display:none!important}
        .main-content{padding:0!important;margin:0!important}
        .report-page{gap:20px;padding:0!important}
        .report-print-head{display:block}
        .report-print-stats-table{display:table}
        .report-stats-container, .report-usage-row{display:none!important}
        .report-section-card{border:none;box-shadow:none;break-inside:avoid;border-radius:0;margin-top:20px}
        .report-section-header{display:block;background:none;border:none;padding:0 0 10px 0}
        .report-section-info{display:block}
        .report-section-icon{display:none}
        .report-section-copy{gap:2px}
    .report-section-title-text{font-size:18px;color:#000}
        .report-section-subtitle-text{max-width:none;font-size:12px;color:#444}
        .report-section-count{display:inline-flex;padding:0;border:none;color:#000;box-shadow:none;background:none;font-size:12px}
        .report-table-wrap{overflow:visible}
        .report-table{border:1px solid #000}
        .report-table th{background:#f0f0f0!important;color:#000!important;border:1px solid #000!important;padding:10px}
        .report-table td{border:1px solid #000!important;padding:10px;color:#000!important}
        .report-stat-card, .report-usage-widget{display:none!important}
        .report-pill-badge{border:1px solid #000;background:transparent!important;color:#000!important}
        .report-pill-badge i{display:none}
        .report-empty-state{padding:24px 0;border:1px solid #000}
        .report-empty-icon{display:none}
        .report-book-cover{width:32px!important;height:44px!important;border-radius:4px!important;border:1px solid #000!important}
        .report-print-section-note{display:block;font-size:11px;color:#444;margin-top:4px}
    }

    .report-page{
        --dbx-primary:#f97316;
        --dbx-primary-hover:#ea580c;
        --dbx-primary-light:#fff7ed;
        --dbx-bg:#f8fafc;
        --dbx-card-bg:#ffffff;
        --dbx-text:#1e293b;
        --dbx-text-muted:#64748b;
        --dbx-border:#e2e8f0;
        --dbx-success:#22c55e;
        --dbx-danger:#ef4444;
        --dbx-warning:#eab308;
        font-family:Inter,ui-sans-serif,system-ui,sans-serif;
        gap:24px;
        padding-bottom:32px;
    }
    .report-toolbar{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:20px;
        flex-wrap:wrap;
        padding-bottom:0;
        border-bottom:none;
    }
    .report-title-wrap,.report-title-copy{gap:4px}
    .report-title{
        font-family:Inter,ui-sans-serif,system-ui,sans-serif;
        font-size:24px;
        font-weight:800;
        letter-spacing:0;
        color:var(--dbx-text);
        line-height:1.2;
    }
    .report-subtitle{
        font-size:14px;
        color:var(--dbx-text-muted);
        font-weight:400;
        margin-top:0;
    }
    .report-actions{display:flex;gap:8px;flex-wrap:wrap}
    .btn-report-action,.btn-apply{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        padding:8px 16px;
        border-radius:8px;
        background:#fff;
        border:1px solid var(--dbx-border);
        color:var(--dbx-text);
        font-size:13px;
        font-weight:600;
        cursor:pointer;
        transition:all .2s;
        text-decoration:none;
        box-shadow:none;
        height:42px;
        font-family:Inter,ui-sans-serif,system-ui,sans-serif;
    }
    .btn-report-action:hover,.btn-apply:hover{
        border-color:var(--dbx-primary);
        color:var(--dbx-primary);
        transform:none;
        box-shadow:none;
    }
    .btn-report-action.primary{
        background:var(--dbx-primary);
        color:#fff;
        border-color:var(--dbx-primary);
        box-shadow:none;
    }
    .btn-report-action.primary:hover{
        background:var(--dbx-primary-hover);
        color:#fff;
        border-color:var(--dbx-primary-hover);
        box-shadow:none;
    }
    .report-tabs{
        display:flex;
        gap:4px;
        padding:4px;
        background:#fff;
        border:1px solid var(--dbx-border);
        border-radius:12px;
        width:fit-content;
    }
    .report-tab{
        padding:10px 20px;
        border-radius:8px;
        font-size:13px;
        font-weight:600;
        color:var(--dbx-text-muted);
        background:transparent;
        border:none;
    }
    .report-tab:hover{
        color:var(--dbx-primary);
        background:var(--dbx-bg);
    }
    .report-tab.active{
        background:var(--dbx-primary-light);
        color:var(--dbx-primary);
        box-shadow:none;
    }
    .report-filter-card{
        background:#fff;
        border:1px solid var(--dbx-border);
        border-radius:12px;
        padding:20px;
        box-shadow:none;
    }
    .report-filter-card::before{
        content:none;
    }
    .report-filter-grid{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
        gap:16px;
        align-items:flex-end;
    }
    .report-micro{
        font-size:12px;
        font-weight:600;
        color:var(--dbx-text-muted);
        text-transform:uppercase;
        letter-spacing:0;
    }
    .report-filter-card .form-select,.report-filter-card .form-input{
        padding:10px 14px!important;
        border:1px solid var(--dbx-border);
        border-radius:8px!important;
        font-size:14px;
        background:var(--dbx-bg);
        color:var(--dbx-text);
        box-shadow:none;
    }
    .report-filter-card .form-select:focus,.report-filter-card .form-input:focus{
        border-color:var(--dbx-primary);
        box-shadow:0 0 0 3px rgba(249,115,22,.1);
        background:#fff;
    }
    .report-stats-container{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:16px;
    }
    .report-stat-card{
        background:#fff;
        border:1px solid var(--dbx-border);
        border-radius:12px;
        padding:20px;
        position:relative;
        overflow:hidden;
        gap:12px;
        box-shadow:none;
        transition:none;
    }
    .report-stat-card::before{
        content:'';
        position:absolute;
        top:0;
        left:0;
        right:0;
        height:4px;
        transform:none;
        background:var(--dbx-border);
    }
    .report-stat-card.books-card::before{background:var(--dbx-primary)}
    .report-stat-card.loans-card::before{background:var(--dbx-warning)}
    .report-stat-card.returns-card::before{background:var(--dbx-success)}
    .report-stat-card:hover{
        transform:none;
        box-shadow:none;
        border-color:var(--dbx-border);
    }
    .report-stat-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:0;
    }
    .report-stat-icon-box{
        width:40px;
        height:40px;
        border-radius:8px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:18px;
    }
    .report-stat-icon-box.books{background:#fff7ed;color:var(--dbx-primary)}
    .report-stat-icon-box.loans{background:#fefce8;color:var(--dbx-warning)}
    .report-stat-icon-box.returns{background:#f0fdf4;color:var(--dbx-success)}
    .report-stat-value{
        font-family:Inter,ui-sans-serif,system-ui,sans-serif;
        font-size:28px;
        font-weight:800;
        color:var(--dbx-text);
        letter-spacing:0;
    }
    .report-stat-label,.report-stat-footer,.report-usage-tag,.report-usage-desc,.report-section-subtitle-text,.report-book-author,.report-isbn,.report-member-name,.report-date-label,.report-staff-badge,.report-note-ok,.report-empty-text{
        color:var(--dbx-text-muted);
    }
    .report-stat-label{
        font-size:13px;
        font-weight:400;
        margin-top:4px;
    }
    .report-stat-footer{
        font-size:12px;
        margin-top:12px;
        gap:4px;
        font-weight:400;
    }
    .report-usage-row{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
        gap:16px;
    }
    .report-usage-widget{
        background:#fff;
        border:1px solid var(--dbx-border);
        border-radius:12px;
        padding:20px;
        min-height:unset;
        box-shadow:none;
        transition:none;
    }
    .report-usage-widget::before{
        opacity:1;
        width:4px;
        background:var(--dbx-primary);
    }
    .report-usage-widget:hover{
        background:#fff;
        transform:none;
        box-shadow:none;
    }
    .report-usage-tag{
        font-size:12px;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:0;
    }
    .report-usage-number{
        font-family:Inter,ui-sans-serif,system-ui,sans-serif;
        font-size:24px;
        font-weight:800;
        color:var(--dbx-text);
    }
    .report-section-card{
        background:#fff;
        border:1px solid var(--dbx-border);
        border-radius:12px;
        box-shadow:none;
        margin-bottom:0;
    }
    .report-section-card:hover{box-shadow:none}
    .report-section-header{
        padding:16px 20px;
        border-bottom:1px solid var(--dbx-border);
        background:#fafafa;
    }
    .report-section-header::before{content:none}
    .report-section-info{
        display:flex;
        align-items:center;
        gap:12px;
    }
    .report-section-icon{
        width:40px;
        height:40px;
        border-radius:8px;
        background:#fff7ed;
        color:var(--dbx-primary);
        box-shadow:none;
    }
    .report-section-title-text{
        font-family:Inter,ui-sans-serif,system-ui,sans-serif;
        font-size:16px;
        font-weight:700;
        color:var(--dbx-text);
    }
    .report-section-subtitle-text{
        font-size:13px;
        max-width:none;
    }
    .report-section-count{
        font-size:12px;
        font-weight:600;
        padding:4px 10px;
        border-radius:6px;
        background:var(--dbx-primary-light);
        color:var(--dbx-primary);
        border:none;
    }
    .report-table{
        width:100%;
        border-collapse:collapse;
        table-layout:auto;
    }
    .report-table th{
        text-align:left;
        padding:12px 16px;
        font-size:11px;
        font-weight:700;
        text-transform:uppercase;
        color:var(--dbx-text-muted);
        background:var(--dbx-bg);
        border-bottom:1px solid var(--dbx-border);
        letter-spacing:0;
    }
    .report-table td{
        padding:14px 16px;
        border-bottom:1px solid var(--dbx-border);
        vertical-align:middle;
        color:var(--dbx-text);
        font-size:14px;
    }
    .report-table tbody tr:nth-child(even){background:transparent}
    .report-table tr:hover td{background:#f8fafc}
    .report-book-title,.report-date-main,.report-return-value{color:var(--dbx-text)}
    .report-category-badge{
        background:#fff7ed;
        color:var(--dbx-primary);
        border-radius:6px;
        font-size:12px;
        font-weight:600;
    }
    .report-stock-available{color:#166534}
    .report-stock-available.low,.report-date-due,.report-note-late{color:#991b1b}
    .report-stock-divider,.report-stock-total{color:var(--dbx-text-muted)}
    .report-pop-row{color:var(--dbx-warning)}
    .report-member-row{
        border-top:1px dashed var(--dbx-border);
        margin-top:8px;
        padding-top:8px;
    }
    .report-member-avatar{
        background:var(--dbx-primary-light);
        color:var(--dbx-primary);
    }
    .report-staff-badge{
        background:#f1f5f9;
        border:1px solid var(--dbx-border);
        border-radius:6px;
    }
    .report-pill-badge{
        padding:4px 10px;
        border-radius:6px;
        font-size:12px;
        font-weight:600;
        gap:4px;
    }
    .report-pill-badge.borrowed{background:#fff7ed;color:var(--dbx-primary)}
    .report-pill-badge.late{background:#fee2e2;color:#991b1b}
    .report-pill-badge.returned{background:#dcfce7;color:#166534}
    .report-return-row{color:var(--dbx-success)}
    .report-book-info-cell{
        display:flex;
        align-items:center;
        gap:12px;
    }
    .report-book-cover{
        width:36px;
        height:50px;
        border-radius:4px;
        background:#f1f5f9;
        color:#cbd5e1;
        border:1px solid var(--dbx-border);
        box-shadow:none;
        position:relative;
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:hidden;
    }
    .report-book-cover img{
        width:100%;
        height:100%;
        object-fit:cover;
        display:block;
    }
    .report-book-cover-fallback{
        width:100%;
        height:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        background:#f1f5f9;
        color:#94a3b8;
        font-size:12px;
        font-weight:700;
    }
    .report-empty-state{
        padding:60px 24px;
        gap:12px;
    }
    .report-empty-icon{
        width:72px;
        height:72px;
        background:#f8fafc;
        border-radius:24px;
        color:#94a3b8;
    }
    @media (max-width:768px){
        .report-toolbar{flex-direction:column;align-items:stretch}
        .report-actions{display:flex;gap:8px}
    }
</style>

<div class="report-page">
    <div class="report-print-head">
        <div class="report-print-header-content">
            <div class="report-print-logo">
                <?php ($appLogo = \App\Models\Setting::appLogoPath()); ?>
                <?php if($appLogo): ?>
                    <img src="<?php echo e(asset($appLogo)); ?>" alt="<?php echo e($appName); ?>">
                <?php else: ?>
                    <div class="report-print-logo-fallback"><?php echo e(substr($appName, 0, 1)); ?></div>
                <?php endif; ?>
            </div>
            <div class="report-print-info">
                <h1><?php echo e($appName); ?></h1>
                <p>Laporan Operasional Perpustakaan</p>
                <p style="font-weight: bold; margin-top: 8px;">Periode: <?php echo e($reportMeta['range_label']); ?></p>
                <p>Dicetak: <?php echo e($reportMeta['printed_at']); ?></p>
            </div>
        </div>
    </div>

    <table class="report-print-stats-table">
        <thead>
            <tr>
                <th colspan="4">Ringkasan Statistik Perpustakaan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold; background: #f9f9f9; width: 25%;">Total Data Buku</td>
                <td style="width: 25%;"><?php echo e(number_format($reportStats['books'])); ?> Judul</td>
                <td style="font-weight: bold; background: #f9f9f9; width: 25%;">Peminjam Aktif</td>
                <td style="width: 25%;"><?php echo e(number_format($usageStats['unique_borrowers'])); ?> Anggota</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f9f9f9;">Total Peminjaman</td>
                <td><?php echo e(number_format($reportStats['loans'])); ?> Transaksi</td>
                <td style="font-weight: bold; background: #f9f9f9;">Buku Sedang Beredar</td>
                <td><?php echo e(number_format($usageStats['books_in_circulation'])); ?> Buku</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f9f9f9;">Total Pengembalian</td>
                <td><?php echo e(number_format($reportStats['returns'])); ?> Transaksi</td>
                <td style="font-weight: bold; background: #f9f9f9;">Favorit (Kategori)</td>
                <td><?php echo e($usageStats['top_category']); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f9f9f9;">Peminjaman Aktif</td>
                <td><?php echo e($reportStats['active_loans']); ?> Masih dipinjam</td>
                <td style="font-weight: bold; background: #f9f9f9;">Buku Terpopuler</td>
                <td><?php echo e($usageStats['top_book']); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="report-toolbar">
        <div class="report-title-wrap">
            <div class="report-title-copy">
                <h1 class="report-title">Laporan</h1>
                <div class="report-subtitle">Rekapitulasi & Analitik Perpustakaan</div>
            </div>
        </div>
        <div class="report-actions">
            <button type="button" class="btn-report-action" onclick="window.print()">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak
            </button>
            <a href="<?php echo e(route('admin.reports.export', array_merge(request()->query(), ['format' => 'excel']), false)); ?>" class="btn-report-action">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Unduh Excel
            </a>
            <a href="<?php echo e(route('admin.reports.export', array_merge(request()->query(), ['format' => 'pdf']), false)); ?>" class="btn-report-action">
                <i data-lucide="file-text" class="w-4 h-4"></i> Unduh PDF
            </a>
        </div>
    </div>

    <section class="report-tabs">
        <button
            type="button"
            class="report-tab <?php echo e($filters['type'] !== 'loans' && $filters['period'] === 'monthly' ? 'active' : ''); ?>"
            onclick="navigateAsyncPage('<?php echo e(route('admin.reports.index', ['period' => 'monthly', 'type' => 'all'], false)); ?>')"
        >
            Laporan Bulanan
        </button>
        <button
            type="button"
            class="report-tab <?php echo e($filters['type'] !== 'loans' && $filters['period'] === 'yearly' ? 'active' : ''); ?>"
            onclick="navigateAsyncPage('<?php echo e(route('admin.reports.index', ['period' => 'yearly', 'type' => 'all'], false)); ?>')"
        >
            Laporan Tahunan
        </button>
        <button
            type="button"
            class="report-tab <?php echo e($filters['type'] === 'loans' ? 'active' : ''); ?>"
            onclick="navigateAsyncPage('<?php echo e(route('admin.reports.index', ['period' => $filters['period'] ?: 'monthly', 'type' => 'loans'], false)); ?>')"
        >
            Statistik Penggunaan
        </button>
    </section>

    <form method="GET" action="<?php echo e(route('admin.reports.index', [], false)); ?>" class="report-filter-card" data-async="true" data-refresh-targets="#asyncPageContent">
        <div class="report-filter-grid">
            <div class="flex flex-col gap-2">
                <label class="report-micro">Jenis Laporan</label>
                <select name="type" class="form-select px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
                    <option value="all" <?php if($filters['type'] === 'all'): echo 'selected'; endif; ?>>Semua laporan</option>
                    <option value="books" <?php if($filters['type'] === 'books'): echo 'selected'; endif; ?>>Data buku</option>
                    <option value="loans" <?php if($filters['type'] === 'loans'): echo 'selected'; endif; ?>>Data peminjaman</option>
                    <option value="returns" <?php if($filters['type'] === 'returns'): echo 'selected'; endif; ?>>Data pengembalian</option>
                </select>
            </div>
            <div class="flex flex-col gap-2">
                <label class="report-micro">Periode</label>
                <select name="period" id="reportPeriod" class="form-select px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
                    <option value="daily" <?php if($filters['period'] === 'daily'): echo 'selected'; endif; ?>>Harian</option>
                    <option value="weekly" <?php if($filters['period'] === 'weekly'): echo 'selected'; endif; ?>>Mingguan</option>
                    <option value="monthly" <?php if($filters['period'] === 'monthly'): echo 'selected'; endif; ?>>Bulanan</option>
                    <option value="yearly" <?php if($filters['period'] === 'yearly'): echo 'selected'; endif; ?>>Tahunan</option>
                    <option value="custom" <?php if($filters['period'] === 'custom'): echo 'selected'; endif; ?>>Custom</option>
                </select>
            </div>
            <div class="js-custom-date flex flex-col gap-2">
                <label class="report-micro">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?php echo e($filters['start_date']); ?>" class="form-input px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
            </div>
            <div class="js-custom-date flex flex-col gap-2">
                <label class="report-micro">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?php echo e($filters['end_date']); ?>" class="form-input px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
            </div>
            <button type="submit" class="btn-apply">Tampilkan</button>
        </div>
    </form>

    <section class="report-stats-container">
        <div class="report-stat-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box books"><i data-lucide="book"></i></div>
                <div class="report-stat-footer"><i data-lucide="calendar"></i> <?php echo e($reportMeta['range_label']); ?></div>
            </div>
            <div class="report-stat-content">
                <div class="report-stat-value"><?php echo e(number_format($reportStats['books'])); ?></div>
                <div class="report-stat-label">Total Data Buku</div>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box loans"><i data-lucide="book-up-2"></i></div>
                <div class="report-stat-footer"><i data-lucide="info"></i> <?php echo e($reportStats['active_loans']); ?> masih aktif</div>
            </div>
            <div class="report-stat-content">
                <div class="report-stat-value"><?php echo e(number_format($reportStats['loans'])); ?></div>
                <div class="report-stat-label">Total Peminjaman</div>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box returns"><i data-lucide="book-down"></i></div>
                <div class="report-stat-footer"><i data-lucide="alert-circle"></i> <?php echo e($reportStats['returned_late']); ?> terlambat</div>
            </div>
            <div class="report-stat-content">
                <div class="report-stat-value"><?php echo e(number_format($reportStats['returns'])); ?></div>
                <div class="report-stat-label">Total Pengembalian</div>
            </div>
        </div>
    </section>

    <section class="report-usage-row">
        <div class="report-usage-widget">
            <div class="report-usage-tag">Peminjam Aktif</div>
            <div class="report-usage-number"><?php echo e(number_format($usageStats['unique_borrowers'])); ?></div>
            <div class="report-usage-desc">Anggota unik</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Buku Dipakai</div>
            <div class="report-usage-number"><?php echo e(number_format($usageStats['books_in_circulation'])); ?></div>
            <div class="report-usage-desc">Sedang beredar</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Selesai</div>
            <div class="report-usage-number"><?php echo e(number_format($usageStats['completed_returns'])); ?></div>
            <div class="report-usage-desc">Pengembalian</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Terpopuler</div>
            <div class="report-usage-number" style="font-size: 16px; line-height: 1.4"><?php echo e($usageStats['top_book']); ?></div>
            <div class="report-usage-desc">Judul buku</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Favorit</div>
            <div class="report-usage-number" style="font-size: 16px; line-height: 1.4"><?php echo e($usageStats['top_category']); ?></div>
            <div class="report-usage-desc">Kategori</div>
        </div>
    </section>

    <?php if(in_array($filters['type'], ['all', 'books'], true)): ?>
        <section class="report-section-card">
            <div class="report-section-header">
                <div class="report-section-info">
                    <div class="report-section-icon"><i data-lucide="book-open" class="w-5 h-5"></i></div>
                    <div class="report-section-copy">
                        <h2 class="report-section-title-text">Data Buku</h2>
                        <p class="report-section-subtitle-text">Daftar koleksi buku yang terdaftar di perpustakaan pada periode laporan ini.</p>
                        <div class="report-print-section-note">Laporan data buku perpustakaan.</div>
                    </div>
                </div>
                <div class="report-section-count"><?php echo e($bookReport->count()); ?> Judul</div>
            </div>
            <?php if($bookReport->count()): ?>
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Informasi Buku</th>
                                <th>Kategori</th>
                                <th>ISBN</th>
                                <th>Stok (Tersedia/Total)</th>
                                <th>Populer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $bookReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="report-book-info-cell">
                                            <div class="report-book-cover">
                                                <?php if($book->cover_image): ?>
                                                    <img
                                                        src="<?php echo e(asset('storage/' . $book->cover_image)); ?>"
                                                        alt="<?php echo e($book->title); ?>"
                                                        loading="lazy"
                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                    >
                                                    <span class="report-book-cover-fallback" style="display:none;">
                                                        <?php echo e(strtoupper(substr($book->title, 0, 2))); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="report-book-cover-fallback">
                                                        <?php echo e(strtoupper(substr($book->title, 0, 2))); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="report-book-title"><?php echo e($book->title); ?></span>
                                                <span class="report-book-author"><?php echo e($book->author); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="report-category-badge">
                                            <?php echo e($book->category?->name ?? 'Umum'); ?>

                                        </span>
                                    </td>
                                    <td class="report-isbn"><?php echo e($book->isbn ?: '-'); ?></td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="report-stock-available <?php echo e($book->stock_available > 0 ? '' : 'low'); ?>"><?php echo e($book->stock_available); ?></span>
                                            <span class="report-stock-divider">/</span>
                                            <span class="report-stock-total"><?php echo e($book->stock_total); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="report-pop-row">
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                            <span class="report-pop-count"><?php echo e($book->loans_count); ?>x</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="report-empty-state">
                    <div class="report-empty-icon"><i data-lucide="book-x" class="w-10 h-10"></i></div>
                    <p class="report-empty-text">Tidak ada data buku pada periode ini.</p>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if(in_array($filters['type'], ['all', 'loans'], true)): ?>
        <section class="report-section-card">
            <div class="report-section-header">
                <div class="report-section-info">
                    <div class="report-section-icon"><i data-lucide="book-up-2" class="w-5 h-5"></i></div>
                    <div class="report-section-copy">
                        <h2 class="report-section-title-text">Data Peminjaman</h2>
                        <p class="report-section-subtitle-text">Riwayat transaksi peminjaman buku yang dilakukan oleh anggota perpustakaan.</p>
                        <div class="report-print-section-note">Laporan transaksi peminjaman buku.</div>
                    </div>
                </div>
                <div class="report-section-count"><?php echo e($loanReport->count()); ?> Transaksi</div>
            </div>
            <?php if($loanReport->count()): ?>
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Buku & Peminjam</th>
                                <th>Waktu Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Petugas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $loanReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="report-book-info-cell">
                                            <div class="report-book-cover">
                                                <?php if($loan->book?->cover_image): ?>
                                                    <img
                                                        src="<?php echo e(asset('storage/' . $loan->book->cover_image)); ?>"
                                                        alt="<?php echo e($loan->book->title); ?>"
                                                        loading="lazy"
                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                    >
                                                    <span class="report-book-cover-fallback" style="display:none;">
                                                        <?php echo e(strtoupper(substr($loan->book?->title ?? 'BK', 0, 2))); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="report-book-cover-fallback">
                                                        <?php echo e(strtoupper(substr($loan->book?->title ?? 'BK', 0, 2))); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <div class="flex flex-col">
                                                    <span class="report-book-title line-clamp-1"><?php echo e($loan->book?->title ?? 'Buku tidak ditemukan'); ?></span>
                                                    <span class="report-book-author"><?php echo e($loan->book?->author ?? '-'); ?></span>
                                                </div>
                                                <div class="report-member-row">
                                                    <div class="report-member-avatar">
                                                        <?php echo e(strtoupper(substr($loan->member?->name ?? 'P', 0, 1))); ?>

                                                    </div>
                                                    <span class="report-member-name"><?php echo e($loan->member?->name ?? 'Peminjam'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="report-date-main"><?php echo e(optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-'); ?></span>
                                            <span class="report-date-label">Tanggal Pinjam</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="report-date-due"><?php echo e(optional($loan->due_at)->translatedFormat('d M Y') ?? '-'); ?></span>
                                            <span class="report-date-label">Harus Kembali</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="report-staff-badge">
                                            <?php echo e($loan->processor?->name ?? '-'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="report-pill-badge <?php echo e($loan->status); ?>">
                                            <i data-lucide="<?php echo e($loan->status === 'returned' ? 'check-circle' : ($loan->status === 'late' ? 'alert-triangle' : 'clock')); ?>" class="w-3.5 h-3.5"></i>
                                            <?php echo e($loan->status === 'late' ? 'Terlambat' : ($loan->status === 'returned' ? 'Selesai' : 'Dipinjam')); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="report-empty-state">
                    <div class="report-empty-icon"><i data-lucide="file-warning" class="w-10 h-10"></i></div>
                    <p class="report-empty-text">Tidak ada data peminjaman pada periode ini.</p>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if(in_array($filters['type'], ['all', 'returns'], true)): ?>
        <section class="report-section-card">
            <div class="report-section-header">
                <div class="report-section-info">
                    <div class="report-section-icon"><i data-lucide="book-down" class="w-5 h-5"></i></div>
                    <div class="report-section-copy">
                        <h2 class="report-section-title-text">Data Pengembalian</h2>
                        <p class="report-section-subtitle-text">Daftar buku yang telah dikembalikan oleh anggota ke perpustakaan.</p>
                        <div class="report-print-section-note">Laporan transaksi pengembalian buku.</div>
                    </div>
                </div>
                <div class="report-section-count"><?php echo e($returnReport->count()); ?> Pengembalian</div>
            </div>
            <?php if($returnReport->count()): ?>
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Buku & Peminjam</th>
                                <th>Waktu Kembali</th>
                                <th>Petugas</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $returnReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="report-book-info-cell">
                                            <div class="report-book-cover">
                                                <?php if($loan->book?->cover_image): ?>
                                                    <img
                                                        src="<?php echo e(asset('storage/' . $loan->book->cover_image)); ?>"
                                                        alt="<?php echo e($loan->book->title); ?>"
                                                        loading="lazy"
                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                    >
                                                    <span class="report-book-cover-fallback" style="display:none;">
                                                        <?php echo e(strtoupper(substr($loan->book?->title ?? 'BK', 0, 2))); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="report-book-cover-fallback">
                                                        <?php echo e(strtoupper(substr($loan->book?->title ?? 'BK', 0, 2))); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <div class="flex flex-col">
                                                    <span class="report-book-title line-clamp-1"><?php echo e($loan->book?->title ?? 'Buku tidak ditemukan'); ?></span>
                                                    <span class="report-book-author"><?php echo e($loan->book?->author ?? '-'); ?></span>
                                                </div>
                                                <div class="report-member-row">
                                                    <span class="report-member-name"><?php echo e($loan->member?->name ?? 'Peminjam'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="report-return-row">
                                            <i data-lucide="calendar-check" class="w-4 h-4"></i>
                                            <span class="report-return-value"><?php echo e(optional($loan->returned_at)->translatedFormat('d M Y') ?? '-'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="report-staff-badge">
                                            <?php echo e($loan->processor?->name ?? '-'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($loan->status === 'late'): ?>
                                            <span class="report-note-late">Dikembalikan Terlambat</span>
                                        <?php else: ?>
                                            <span class="report-note-ok">Tepat Waktu</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="report-empty-state">
                    <div class="report-empty-icon"><i data-lucide="clipboard-x" class="w-10 h-10"></i></div>
                    <p class="report-empty-text">Tidak ada data pengembalian pada periode ini.</p>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periodSelect = document.getElementById('reportPeriod');
        const customDateFields = document.querySelectorAll('.js-custom-date');

        function toggleCustomDates() {
            const isCustom = periodSelect.value === 'custom';
            customDateFields.forEach(field => {
                field.style.display = isCustom ? 'flex' : 'none';
            });
        }

        if (periodSelect) {
            periodSelect.addEventListener('change', toggleCustomDates);
            toggleCustomDates();
        }

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/admin/reports/index.blade.php ENDPATH**/ ?>