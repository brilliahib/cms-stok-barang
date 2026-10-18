<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-1">Riwayat Transaksi Kasir</h2>
                <p class="text-secondary mb-0">Cari transaksi yang sudah selesai berdasarkan tanggal, nomor nota, atau item.</p>
            </div>
            <a href="{{ route('kasir.index') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Transaksi baru
            </a>
        </div>
    </x-slot>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h3 class="h5 mb-0">Filter transaksi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('kasir.history') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label for="transaction-search" class="form-label">Nomor nota atau nama item</label>
                    <input id="transaction-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Contoh: KSR-20261018 atau jasa servis">
                </div>
                <div class="col-6 col-lg-3">
                    <label for="tanggal-awal" class="form-label">Tanggal awal</label>
                    <input id="tanggal-awal" type="date" name="tanggal_awal" value="{{ $filters['tanggal_awal'] ?? '' }}" class="form-control @error('tanggal_awal') is-invalid @enderror">
                    @error('tanggal_awal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-lg-3">
                    <label for="tanggal-akhir" class="form-label">Tanggal akhir</label>
                    <input id="tanggal-akhir" type="date" name="tanggal_akhir" value="{{ $filters['tanggal_akhir'] ?? '' }}" class="form-control @error('tanggal_akhir') is-invalid @enderror">
                    @error('tanggal_akhir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
                    <a href="{{ route('kasir.history') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="small text-secondary">{{ number_format($transaksis->total(), 0, ',', '.') }} transaksi ditemukan</span>
                    <a href="{{ route('kasir.transaksi.export', array_filter($filters, fn ($value) => filled($value))) }}" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i> Ekspor hasil ke PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h3 class="h5 mb-0">Transaksi selesai</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nomor nota</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Item</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col"><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksis as $transaksi)
                            <tr>
                                <td class="fw-semibold">{{ $transaksi->reference }}</td>
                                <td>{{ $transaksi->completed_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <div>{{ number_format($transaksi->items->count(), 0, ',', '.') }} baris</div>
                                    <div class="small text-secondary">{{ $transaksi->items->pluck('item_name')->take(2)->implode(', ') }}{{ $transaksi->items->count() > 2 ? ', …' : '' }}</div>
                                </td>
                                <td class="text-end fw-semibold">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('kasir.transaksi.show', $transaksi) }}" class="btn btn-sm btn-outline-primary">
                                        Lihat nota <span class="visually-hidden">{{ $transaksi->reference }}</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-receipt fs-2 text-secondary" aria-hidden="true"></i>
                                    <p class="fw-semibold mt-2 mb-1">Transaksi tidak ditemukan</p>
                                    <p class="text-secondary mb-0">Ubah kata pencarian atau rentang tanggal, atau buat transaksi baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transaksis->hasPages())
                <div class="mt-3">{{ $transaksis->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
