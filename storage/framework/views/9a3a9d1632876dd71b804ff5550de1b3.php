<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($reportMeta['title']); ?></title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { border-bottom: 3px double #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; border: none; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .logo-wrap { width: 130px; }
        .logo { display: block; width: auto; height: 72px; max-width: 120px; object-fit: contain; object-position: left center; }
        .app-name { font-size: 22px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .report-info { font-size: 14px; font-weight: bold; margin-top: 5px; }
        .meta-info { font-size: 11px; color: #555; margin-top: 3px; }
        
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .stats-table td { border: 1px solid #000; padding: 10px; text-align: center; width: 20%; }
        .stat-val { font-size: 16px; font-weight: bold; display: block; }
        .stat-lbl { font-size: 9px; text-transform: uppercase; color: #666; }

        h3 { font-size: 14px; border-left: 5px solid #333; padding-left: 10px; margin: 25px 0 10px 0; background: #f5f5f5; padding: 5px 10px; }
        
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th { background: #eee; border: 1px solid #000; padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.data-table td { border: 1px solid #000; padding: 7px; font-size: 11px; vertical-align: top; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <?php if($reportMeta['app_logo']): ?>
                    <td class="logo-wrap">
                        <img src="<?php echo e($reportMeta['app_logo']); ?>" class="logo">
                    </td>
                <?php endif; ?>
                <td>
                    <div class="app-name"><?php echo e($reportMeta['app_name']); ?></div>
                    <div class="report-info">Laporan Perpustakaan | <?php echo e($reportMeta['title']); ?></div>
                    <div class="meta-info">Periode: <?php echo e($reportMeta['range_label']); ?></div>
                    <div class="meta-info">Dicetak: <?php echo e($reportMeta['printed_at']); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <span class="stat-val"><?php echo e(number_format($reportStats['books'])); ?></span>
                <span class="stat-lbl">Total Buku</span>
            </td>
            <td>
                <span class="stat-val"><?php echo e(number_format($reportStats['loans'])); ?></span>
                <span class="stat-lbl">Peminjaman</span>
            </td>
            <td>
                <span class="stat-val"><?php echo e(number_format($reportStats['returns'])); ?></span>
                <span class="stat-lbl">Pengembalian</span>
            </td>
            <td>
                <span class="stat-val"><?php echo e(number_format($reportStats['returned_late'])); ?></span>
                <span class="stat-lbl">Terlambat</span>
            </td>
            <td>
                <span class="stat-val"><?php echo e(number_format($usageStats['unique_borrowers'])); ?></span>
                <span class="stat-lbl">Peminjam</span>
            </td>
        </tr>
    </table>

    <?php if(in_array($filters['type'], ['all', 'books'], true)): ?>
        <h3>Data Koleksi Buku</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Judul Buku</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th style="text-align: center;">Stok</th>
                    <th style="text-align: center;">Pinjam</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $bookReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e($book->title); ?></strong></td>
                        <td><?php echo e($book->author); ?></td>
                        <td><?php echo e($book->category?->name ?? '-'); ?></td>
                        <td style="text-align: center;"><?php echo e($book->stock_available); ?> / <?php echo e($book->stock_total); ?></td>
                        <td style="text-align: center;"><?php echo e($book->loans_count); ?>x</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if(in_array($filters['type'], ['all', 'loans'], true)): ?>
        <h3>Data Transaksi Peminjaman</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Buku</th>
                    <th>Peminjam</th>
                    <th>Tgl Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Petugas</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $loanReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($loan->book?->title ?? '-'); ?></td>
                        <td><?php echo e($loan->member?->name ?? '-'); ?></td>
                        <td><?php echo e($loan->borrowed_at->translatedFormat('d M Y')); ?></td>
                        <td><?php echo e($loan->due_at->translatedFormat('d M Y')); ?></td>
                        <td><?php echo e($loan->processor?->name ?? '-'); ?></td>
                        <td>
                            <?php if($loan->status === 'borrowed'): ?> Dipinjam
                            <?php elseif($loan->status === 'late'): ?> Terlambat
                            <?php elseif($loan->status === 'returned'): ?> Kembali
                            <?php else: ?> <?php echo e($loan->status); ?>

                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if(in_array($filters['type'], ['all', 'returns'], true)): ?>
        <h3>Data Pengembalian Buku</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Buku</th>
                    <th>Peminjam</th>
                    <th>Tgl Kembali</th>
                    <th>Petugas</th>
                    <th>Kondisi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $returnReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($loan->book?->title ?? '-'); ?></td>
                        <td><?php echo e($loan->member?->name ?? '-'); ?></td>
                        <td><?php echo e($loan->returned_at?->translatedFormat('d M Y') ?? '-'); ?></td>
                        <td><?php echo e($loan->processor?->name ?? '-'); ?></td>
                        <td><?php echo e($loan->status === 'late' ? 'Terlambat' : 'Tepat Waktu'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem Perpustakaan - <?php echo e(date('Y')); ?>

    </div>
</body>
</html>
<?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views\admin\reports\export-pdf.blade.php ENDPATH**/ ?>