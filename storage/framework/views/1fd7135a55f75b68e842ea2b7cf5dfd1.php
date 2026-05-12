<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { border-bottom: 3px double #333; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { width: 60px; height: 60px; vertical-align: middle; margin-right: 15px; }
        .title { font-size: 20px; font-weight: bold; display: inline-block; vertical-align: middle; text-transform: uppercase; }
        .meta { margin-top: 10px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <?php if($reportMeta['app_logo']): ?>
            <img src="<?php echo e($reportMeta['app_logo']); ?>" class="logo">
        <?php endif; ?>
        <div class="title"><?php echo e($reportMeta['app_name']); ?></div>
        <div class="meta">
            <strong><?php echo e($reportMeta['title']); ?></strong><br>
            Periode: <?php echo e($reportMeta['range_label']); ?> | Dicetak: <?php echo e($reportMeta['printed_at']); ?>

        </div>
    </div>

    <?php if(in_array($filters['type'], ['all', 'books'], true)): ?>
        <h3>Data Buku</h3>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Pinjam</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $bookReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($book->title); ?></td>
                        <td><?php echo e($book->author); ?></td>
                        <td><?php echo e($book->category?->name ?? '-'); ?></td>
                        <td><?php echo e($book->stock_available); ?> / <?php echo e($book->stock_total); ?></td>
                        <td><?php echo e($book->loans_count); ?>x</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if(in_array($filters['type'], ['all', 'loans'], true)): ?>
        <h3>Data Peminjaman</h3>
        <table>
            <thead>
                <tr>
                    <th>Buku</th>
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
        <h3>Data Pengembalian</h3>
        <table>
            <thead>
                <tr>
                    <th>Buku</th>
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
</body>
</html>
<?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views\admin\reports\export-excel.blade.php ENDPATH**/ ?>