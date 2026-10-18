<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-1">Kasir</h2>
                <p class="text-secondary mb-0">Pilih barang katalog atau tambahkan jasa dan item lainnya.</p>
            </div>
            <a href="{{ route('kasir.history') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-1" aria-hidden="true"></i> Riwayat Transaksi
            </a>
        </div>
    </x-slot>

    <div x-data="kasirCart({
        products: @js($products->getCollection()->map(fn ($product) => ['id' => $product->id, 'name' => $product->nama_barang, 'stock' => $product->jumlah])->values()),
        oldItems: @js(old('items', [])),
        oldAmount: @js(old('amount_received', ''))
    })">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold">Transaksi belum dapat diproses.</div>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <div class="row g-4 align-items-start">
            <section class="col-12 col-xl-7" aria-labelledby="catalog-heading">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h3 id="catalog-heading" class="h5 mb-1">Katalog barang</h3>
                                <p class="text-secondary mb-0">Stok diperiksa kembali saat checkout.</p>
                            </div>
                            <span class="badge text-bg-light">{{ $products->total() }} barang</span>
                        </div>

                        <form method="GET" action="{{ route('kasir.index') }}" role="search">
                            <label for="product-search" class="form-label">Cari nama barang</label>
                            <div class="input-group">
                                <input id="product-search" type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Contoh: oli, kampas rem">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search me-1" aria-hidden="true"></i> Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-3">
                    @forelse ($products as $product)
                        <div class="col">
                            <article class="card h-100 shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <h4 class="h6 mb-2">{{ $product->nama_barang }}</h4>
                                        <span class="badge {{ $product->jumlah > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            Stok {{ number_format($product->jumlah, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <p class="text-secondary small mb-3">Harga jual diisi pada keranjang.</p>
                                    <button
                                        type="button"
                                        class="btn btn-primary mt-auto"
                                        @click="addCatalog({ id: {{ $product->id }}, name: @js($product->nama_barang), stock: {{ $product->jumlah }} })"
                                        @disabled($product->jumlah < 1)
                                        aria-label="Tambah {{ $product->nama_barang }} ke keranjang"
                                    >
                                        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah
                                    </button>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card border-0">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-search fs-2 text-secondary" aria-hidden="true"></i>
                                    <p class="fw-semibold mt-2 mb-1">Barang tidak ditemukan</p>
                                    <p class="text-secondary mb-0">Coba kata pencarian lain atau tambahkan item manual ke keranjang.</p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($products->hasPages())
                    <div class="mt-3">{{ $products->links() }}</div>
                @endif
            </section>

            <section class="col-12 col-xl-5" aria-labelledby="cart-heading">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <h3 id="cart-heading" class="h5 mb-0">Keranjang</h3>
                            <span class="small text-secondary" x-text="`${items.length} baris`" aria-live="polite"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addCustom()">
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Item manual
                        </button>
                    </div>

                    <form method="POST" action="{{ route('kasir.checkout') }}">
                        @csrf
                        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">

                        <div class="card-body">
                            <template x-if="items.length === 0">
                                <div class="text-center text-secondary py-4">
                                    <i class="bi bi-basket2 fs-2" aria-hidden="true"></i>
                                    <p class="mb-0 mt-2">Keranjang masih kosong.</p>
                                    <p class="small mb-0">Pilih barang atau tambahkan item manual.</p>
                                </div>
                            </template>

                            <template x-for="(item, index) in items" :key="item.key">
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="flex-grow-1">
                                            <span class="badge mb-2" :class="item.type === 'catalog' ? 'text-bg-primary' : 'text-bg-secondary'" x-text="item.type === 'catalog' ? 'Katalog' : 'Manual'"></span>
                                            <template x-if="item.type === 'catalog'">
                                                <div class="fw-semibold" x-text="item.name"></div>
                                            </template>
                                            <template x-if="item.type === 'custom'">
                                                <div>
                                                    <label class="form-label small mb-1" :for="`item-name-${index}`">Nama barang atau jasa</label>
                                                    <input :id="`item-name-${index}`" type="text" maxlength="255" class="form-control" :name="`items[${index}][name]`" x-model="item.name" required>
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="remove(index)" :aria-label="`Hapus ${item.name || 'item manual'}`">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>

                                    <input type="hidden" :name="`items[${index}][type]`" :value="item.type">
                                    <input type="hidden" :name="`items[${index}][barang_id]`" :value="item.type === 'catalog' ? item.barang_id : ''">
                                    <template x-if="item.type === 'catalog'">
                                        <input type="hidden" :name="`items[${index}][name]`" :value="item.name">
                                    </template>

                                    <div class="row g-2">
                                        <div class="col-5">
                                            <label class="form-label small" :for="`item-quantity-${index}`">Jumlah</label>
                                            <input :id="`item-quantity-${index}`" type="number" min="1" max="2147483647" step="1" class="form-control" :name="`items[${index}][quantity]`" x-model="item.quantity" required>
                                        </div>
                                        <div class="col-7">
                                            <label class="form-label small" :for="`item-price-${index}`">Harga satuan (Rp)</label>
                                            <input :id="`item-price-${index}`" type="number" min="1" max="99999999" step="1" class="form-control" :name="`items[${index}][unit_price]`" x-model="item.unit_price" required>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 small">
                                        <span class="text-secondary">Subtotal item</span>
                                        <span class="fw-semibold" x-text="formatRupiah(lineTotal(item))"></span>
                                    </div>
                                </div>
                            </template>

                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total</span>
                                    <span class="h5 mb-0" x-text="formatRupiah(total)" aria-live="polite"></span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white">
                            <label for="amount-received" class="form-label">Uang diterima (Rp)</label>
                            <input id="amount-received" type="number" min="1" max="999999999999999999" step="1" name="amount_received" class="form-control" x-model="amountReceived" required>
                            <div class="d-flex justify-content-between mt-2 mb-3">
                                <span class="text-secondary">Kembalian</span>
                                <span class="fw-semibold" x-text="formatRupiah(change)" aria-live="polite"></span>
                            </div>
                            <button type="submit" class="btn btn-success w-100" :disabled="items.length === 0">
                                <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Checkout dan buat nota
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            window.kasirCart = function (config) {
                const makeKey = () => `${Date.now()}-${Math.random().toString(36).slice(2)}`;
                const restored = Array.isArray(config.oldItems) ? config.oldItems : [];

                return {
                    items: restored.map((item) => ({
                        key: makeKey(),
                        type: item.type,
                        barang_id: item.barang_id || '',
                        name: item.name || '',
                        quantity: Number(item.quantity || 1),
                        unit_price: item.unit_price || '',
                    })),
                    amountReceived: config.oldAmount || '',
                    init() {
                        const storageKey = `shr-kasir-cart-{{ auth()->id() }}`;
                        try {
                            const saved = sessionStorage.getItem(storageKey);
                            if (this.items.length === 0 && saved) {
                                const cart = JSON.parse(saved);
                                this.items = Array.isArray(cart.items) ? cart.items : [];
                                this.amountReceived = cart.amountReceived || '';
                            }
                            this.$watch('items', () => this.save(storageKey));
                            this.$watch('amountReceived', () => this.save(storageKey));
                            this.save(storageKey);
                        } catch (error) {
                            // Cart persistence is optional when browser storage is unavailable.
                        }
                    },
                    save(storageKey) {
                        try {
                            sessionStorage.setItem(storageKey, JSON.stringify({ items: this.items, amountReceived: this.amountReceived }));
                        } catch (error) {
                            // Keep checkout usable when browser storage is unavailable.
                        }
                    },
                    addCatalog(product) {
                        const existing = this.items.find((item) => item.type === 'catalog' && Number(item.barang_id) === Number(product.id));
                        if (existing) {
                            if (Number(existing.quantity) < Number(product.stock)) existing.quantity = Number(existing.quantity) + 1;
                            return;
                        }
                        this.items.push({ key: makeKey(), type: 'catalog', barang_id: product.id, name: product.name, quantity: 1, unit_price: '' });
                    },
                    addCustom() {
                        this.items.push({ key: makeKey(), type: 'custom', barang_id: '', name: '', quantity: 1, unit_price: '' });
                    },
                    remove(index) {
                        this.items.splice(index, 1);
                    },
                    lineTotal(item) {
                        return this.toBigInt(item.quantity) * this.toBigInt(item.unit_price);
                    },
                    get total() {
                        return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0n);
                    },
                    get change() {
                        const received = this.toBigInt(this.amountReceived);
                        return received > this.total ? received - this.total : 0n;
                    },
                    toBigInt(value) {
                        const digits = String(value ?? '');
                        return /^\d+$/.test(digits) ? BigInt(digits) : 0n;
                    },
                    formatRupiah(value) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0n);
                    },
                };
            };
        </script>
    @endpush
</x-app-layout>
