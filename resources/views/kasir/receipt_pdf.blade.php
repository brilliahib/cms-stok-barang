<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota {{ $transaksi->reference }}</title>
    <style>
        @page { size: A5 portrait; margin: 12mm; }
        body { color: #212529; font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { font-size: 17pt; margin: 0; }
        p { margin: 3px 0; }
        .muted { color: #666; }
        .receipt-meta { border-bottom: 1px solid #dee2e6; margin: 12px 0; padding-bottom: 10px; }
        table { border-collapse: collapse; margin: 12px 0; width: 100%; }
        th, td { border-bottom: 1px solid #dee2e6; padding: 7px 4px; }
        th { font-size: 9pt; text-align: left; }
        .number { text-align: right; white-space: nowrap; }
        .summary { margin-left: auto; width: 65%; }
        .summary td { border: 0; padding: 4px; }
        .grand-total { border-top: 1px solid #212529 !important; font-size: 12pt; font-weight: bold; }
        .thanks { border-top: 1px solid #dee2e6; margin-top: 14px; padding-top: 10px; text-align: center; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>SHR Jaya Motor</h1>
    <p class="muted">Nota penjualan</p>

    <div class="receipt-meta">
        <p><strong>Nomor:</strong> {{ $transaksi->reference }}</p>
        <p><strong>Tanggal:</strong> {{ $transaksi->completed_at->format('d M Y, H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="number">Qty</th>
                <th class="number">Harga</th>
                <th class="number">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td class="number">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td class="number">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="number">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tbody>
            <tr class="grand-total"><td>Total</td><td class="number">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td></tr>
            <tr><td>Uang diterima</td><td class="number">Rp {{ number_format($transaksi->amount_received, 0, ',', '.') }}</td></tr>
            <tr><td>Kembalian</td><td class="number">Rp {{ number_format($transaksi->change_amount, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>

    <p class="thanks">Terima kasih telah berbelanja.</p>
</body>
</html>
