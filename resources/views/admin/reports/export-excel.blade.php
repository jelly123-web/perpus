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
        @if($reportMeta['app_logo'])
            <img src="{{ $reportMeta['app_logo'] }}" class="logo">
        @endif
        <div class="title">{{ $reportMeta['app_name'] }}</div>
        <div class="meta">
            <strong>{{ $reportMeta['title'] }}</strong><br>
            Periode: {{ $reportMeta['range_label'] }} | Dicetak: {{ $reportMeta['printed_at'] }}
        </div>
    </div>

    @if (in_array($filters['type'], ['all', 'books'], true))
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
                @foreach ($bookReport as $book)
                    <tr>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author }}</td>
                        <td>{{ $book->category?->name ?? '-' }}</td>
                        <td>{{ $book->stock_available }} / {{ $book->stock_total }}</td>
                        <td>{{ $book->loans_count }}x</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (in_array($filters['type'], ['all', 'loans'], true))
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
</body>
</html>
