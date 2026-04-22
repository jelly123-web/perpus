<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportMeta['title'] }}</title>
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
                @if($reportMeta['app_logo'])
                    <td class="logo-wrap">
                        <img src="{{ $reportMeta['app_logo'] }}" class="logo">
                    </td>
                @endif
                <td>
                    <div class="app-name">{{ $reportMeta['app_name'] }}</div>
                    <div class="report-info">Laporan Perpustakaan | {{ $reportMeta['title'] }}</div>
                    <div class="meta-info">Periode: {{ $reportMeta['range_label'] }}</div>
                    <div class="meta-info">Dicetak: {{ $reportMeta['printed_at'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <span class="stat-val">{{ number_format($reportStats['books']) }}</span>
                <span class="stat-lbl">Total Buku</span>
            </td>
            <td>
                <span class="stat-val">{{ number_format($reportStats['loans']) }}</span>
                <span class="stat-lbl">Peminjaman</span>
            </td>
            <td>
                <span class="stat-val">{{ number_format($reportStats['returns']) }}</span>
                <span class="stat-lbl">Pengembalian</span>
            </td>
            <td>
                <span class="stat-val">{{ number_format($reportStats['returned_late']) }}</span>
                <span class="stat-lbl">Terlambat</span>
            </td>
            <td>
                <span class="stat-val">{{ number_format($usageStats['unique_borrowers']) }}</span>
                <span class="stat-lbl">Peminjam</span>
            </td>
        </tr>
    </table>

    @if (in_array($filters['type'], ['all', 'books'], true))
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
                @foreach ($bookReport as $book)
                    <tr>
                        <td><strong>{{ $book->title }}</strong></td>
                        <td>{{ $book->author }}</td>
                        <td>{{ $book->category?->name ?? '-' }}</td>
                        <td style="text-align: center;">{{ $book->stock_available }} / {{ $book->stock_total }}</td>
                        <td style="text-align: center;">{{ $book->loans_count }}x</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (in_array($filters['type'], ['all', 'loans'], true))
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
                @foreach ($loanReport as $loan)
                    <tr>
                        <td>{{ $loan->book?->title ?? '-' }}</td>
                        <td>{{ $loan->member?->name ?? '-' }}</td>
                        <td>{{ $loan->borrowed_at->translatedFormat('d M Y') }}</td>
                        <td>{{ $loan->due_at->translatedFormat('d M Y') }}</td>
                        <td>{{ $loan->processor?->name ?? '-' }}</td>
                        <td>
                            @if($loan->status === 'borrowed') Dipinjam
                            @elseif($loan->status === 'late') Terlambat
                            @elseif($loan->status === 'returned') Kembali
                            @else {{ $loan->status }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (in_array($filters['type'], ['all', 'returns'], true))
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
                @foreach ($returnReport as $loan)
                    <tr>
                        <td>{{ $loan->book?->title ?? '-' }}</td>
                        <td>{{ $loan->member?->name ?? '-' }}</td>
                        <td>{{ $loan->returned_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>{{ $loan->processor?->name ?? '-' }}</td>
                        <td>{{ $loan->status === 'late' ? 'Terlambat' : 'Tepat Waktu' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak secara otomatis oleh Sistem Perpustakaan - {{ date('Y') }}
    </div>
</body>
</html>
