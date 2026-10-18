<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-1">Nota Transaksi</h2>
                <p class="text-secondary mb-0">{{ $transaksi->reference }} · {{ $transaksi->completed_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('kasir.transaksi.nota', $transaksi) }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i> Unduh PDF
                </a>
                <a href="{{ route('kasir.index') }}" class="btn btn-outline-secondary">Transaksi baru</a>
            </div>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xxl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h3 class="h5 mb-1">SHR Jaya Motor</h3>
                        <span class="text-secondary">Nota penjualan</span>
                    </div>
                    <div class="text-md-end">
                        <div class="small text-secondary">Nomor transaksi</div>
                        <div class="fw-semibold">{{ $transaksi->reference }}</div>
                        <div class="small text-secondary">{{ $transaksi->completed_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Harga satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaksi->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->item_name }}</div>
                                            <span class="small text-secondary">{{ $item->item_type === 'custom' ? 'Item manual' : 'Barang katalog' }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-12 col-md-6 col-lg-5">
                            <dl class="row mb-0">
                                <dt class="col-7">Total</dt>
                                <dd class="col-5 text-end fw-bold">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</dd>
                                <dt class="col-7 text-secondary">Uang diterima</dt>
                                <dd class="col-5 text-end">Rp {{ number_format($transaksi->amount_received, 0, ',', '.') }}</dd>
                                <dt class="col-7 text-secondary">Kembalian</dt>
                                <dd class="col-5 text-end">Rp {{ number_format($transaksi->change_amount, 0, ',', '.') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-center text-secondary small">
                    Terima kasih telah berbelanja.
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                sessionStorage.removeItem('shr-kasir-cart-{{ auth()->id() }}');
            }, { once: true });
        </script>
    @endpush
</x-app-layout>
