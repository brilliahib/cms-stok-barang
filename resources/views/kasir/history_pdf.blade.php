<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat Transaksi Kasir</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { color: #212529; font-family: DejaVu Sans, sans-serif; font-size: 9pt; }
        h1 { font-size: 17pt; margin: 0 0 4px; }
        .muted { color: #666; }
        table { border-collapse: collapse; margin-top: 14px; width: 100%; }
        thead { display: table-header-group; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f4f7f6; }
        .number { text-align: right; white-space: nowrap; }
        .summary { margin: 12px 0; text-align: right; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Riwayat Transaksi Kasir</h1>
    <p class="muted">Dicetak {{ now()->format('d M Y, H:i') }}</p>
    @if (($filters['tanggal_awal'] ?? null) || ($filters['tanggal_akhir'] ?? null))
        <p class="muted">Periode: {{ $filters['tanggal_awal'] ?? 'Awal' }} s/d {{ $filters['tanggal_akhir'] ?? 'Sekarang' }}</p>
    @endif
    @if ($filters['q'] ?? null)
        <p class="muted">Pencarian: {{ $filters['q'] }}</p>
    @endif

    <p class="summary">{{ number_format($transaksis->count(), 0, ',', '.') }} transaksi · Total penjualan: <strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></p>

    <table>
        <thead>
            <tr>
                <th>Nomor nota</th>
                <th>Tanggal</th>
                <th>Item</th>
                <th class="number">Total</th>
                <th class="number">Diterima</th>
                <th class="number">Kembalian</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $transaksi)
                <tr>
                    <td>{{ $transaksi->reference }}</td>
                    <td>{{ $transaksi->completed_at->format('d M Y, H:i') }}</td>
                    <td>
                        @foreach ($transaksi->items as $item)
                            {{ $item->item_name }} × {{ number_format($item->quantity, 0, ',', '.') }}<br>
                        @endforeach
                    </td>
                    <td class="number">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                    <td class="number">Rp {{ number_format($transaksi->amount_received, 0, ',', '.') }}</td>
                    <td class="number">Rp {{ number_format($transaksi->change_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada transaksi yang cocok dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
