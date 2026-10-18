<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KasirTransaksi;
use App\Models\KasirTransaksiItem;
use App\Models\PergerakanBarang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    private const MAX_TRANSACTION_TOTAL = 999999999999999999;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $products = Barang::query()
            ->select(['id', 'nama_barang', 'jumlah'])
            ->when($validated['q'] ?? null, function ($query, $search) {
                $query->where('nama_barang', 'like', '%' . $search . '%');
            })
            ->orderBy('nama_barang')
            ->paginate(12)
            ->withQueryString();

        return view('kasir.index', [
            'products' => $products,
            'submissionKey' => (string) Str::uuid(),
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'submission_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.type' => ['required', 'in:catalog,custom'],
            'items.*.barang_id' => ['nullable', 'integer', 'min:1'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'items.*.unit_price' => ['required', 'integer', 'min:1', 'max:99999999'],
            'amount_received' => ['required', 'integer', 'min:1', 'max:' . self::MAX_TRANSACTION_TOTAL],
        ], [
            'items.required' => 'Tambahkan setidaknya satu barang atau jasa.',
            'items.min' => 'Tambahkan setidaknya satu barang atau jasa.',
            'items.*.quantity.min' => 'Jumlah setiap item minimal 1.',
            'items.*.unit_price.min' => 'Harga setiap item minimal Rp 1.',
            'items.*.unit_price.max' => 'Harga per unit maksimal Rp 99.999.999.',
        ]);

        foreach ($validated['items'] as $index => $item) {
            if ($item['type'] === 'catalog' && empty($item['barang_id'])) {
                throw ValidationException::withMessages([
                    "items.$index.barang_id" => 'Pilih barang dari katalog.',
                ]);
            }

            if ($item['type'] === 'custom' && blank(trim($item['name'] ?? ''))) {
                throw ValidationException::withMessages([
                    "items.$index.name" => 'Masukkan nama barang atau jasa.',
                ]);
            }
        }

        if ($existing = KasirTransaksi::where('submission_key', $validated['submission_key'])->first()) {
            return redirect()->route('kasir.transaksi.show', $existing);
        }

        $lines = [];
        $total = 0;
        $requestedQuantities = [];

        foreach ($validated['items'] as $index => $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = (int) $item['unit_price'];

            if ($quantity > intdiv(self::MAX_TRANSACTION_TOTAL, $unitPrice)) {
                throw ValidationException::withMessages([
                    "items.$index.quantity" => 'Jumlah dan harga item melebihi batas transaksi.',
                ]);
            }

            $lineTotal = $quantity * $unitPrice;
            if ($total > self::MAX_TRANSACTION_TOTAL - $lineTotal) {
                throw ValidationException::withMessages([
                    'items' => 'Total transaksi melebihi batas yang dapat diproses.',
                ]);
            }

            $total += $lineTotal;
            $lines[] = [
                'type' => $item['type'],
                'barang_id' => $item['type'] === 'catalog' ? (int) $item['barang_id'] : null,
                'name' => $item['type'] === 'custom' ? trim($item['name']) : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];

            if ($item['type'] === 'catalog') {
                $barangId = (int) $item['barang_id'];
                $requestedQuantities[$barangId] = ($requestedQuantities[$barangId] ?? 0) + $quantity;
            }
        }

        $amountReceived = (int) $validated['amount_received'];
        if ($amountReceived < $total) {
            throw ValidationException::withMessages([
                'amount_received' => 'Uang yang diterima belum mencukupi total transaksi.',
            ]);
        }

        ksort($requestedQuantities, SORT_NUMERIC);

        try {
            $transaction = DB::transaction(function () use ($validated, $lines, $requestedQuantities, $total, $amountReceived) {
                if ($existing = KasirTransaksi::where('submission_key', $validated['submission_key'])->first()) {
                    return $existing;
                }

                $products = collect();
                if ($requestedQuantities !== []) {
                    $products = Barang::query()
                        ->whereIn('id', array_keys($requestedQuantities))
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    foreach ($requestedQuantities as $barangId => $quantity) {
                        $product = $products->get($barangId);
                        if (! $product) {
                            throw ValidationException::withMessages([
                                'items' => 'Salah satu barang tidak lagi tersedia di katalog.',
                            ]);
                        }

                        if ($quantity > (int) $product->jumlah) {
                            throw ValidationException::withMessages([
                                'items' => "Stok {$product->nama_barang} tidak mencukupi. Stok tersedia: {$product->jumlah}.",
                            ]);
                        }
                    }
                }

                $completedAt = now();
                $reference = 'KSR-' . $completedAt->format('Ymd') . '-' . Str::upper(Str::random(8));
                $sale = KasirTransaksi::create([
                    'reference' => $reference,
                    'submission_key' => $validated['submission_key'],
                    'completed_at' => $completedAt,
                    'total' => $total,
                    'amount_received' => $amountReceived,
                    'change_amount' => $amountReceived - $total,
                    'payment_method' => 'cash',
                ]);

                foreach ($lines as $line) {
                    $product = $line['type'] === 'catalog' ? $products->get($line['barang_id']) : null;

                    KasirTransaksiItem::create([
                        'kasir_transaksi_id' => $sale->id,
                        'barang_id' => $product?->id,
                        'item_type' => $line['type'],
                        'item_name' => $product?->nama_barang ?? $line['name'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['line_total'],
                    ]);

                    if ($product) {
                        PergerakanBarang::create([
                            'barang_id' => $product->id,
                            'jumlah' => $line['quantity'],
                            'harga_satuan' => $line['unit_price'],
                            'tipe' => 'keluar',
                            'tanggal' => $completedAt->toDateString(),
                            'keterangan' => 'Penjualan kasir ' . $reference,
                        ]);
                    }
                }

                foreach ($requestedQuantities as $barangId => $quantity) {
                    $product = $products->get($barangId);
                    $product->jumlah -= $quantity;
                    $product->save();
                }

                return $sale;
            });
        } catch (QueryException $exception) {
            $existing = KasirTransaksi::where('submission_key', $validated['submission_key'])->first();
            if (! $existing) {
                throw $exception;
            }

            $transaction = $existing;
        }

        return redirect()->route('kasir.transaksi.show', $transaction);
    }

    public function show(KasirTransaksi $transaksi)
    {
        $transaksi->load('items');

        return view('kasir.receipt', compact('transaksi'));
    }

    public function history(Request $request)
    {
        $filters = $this->validatedHistoryFilters($request);
        $transaksis = $this->historyQuery($filters)
            ->paginate(20)
            ->withQueryString();

        return view('kasir.history', compact('transaksis', 'filters'));
    }

    public function receiptPdf(KasirTransaksi $transaksi)
    {
        $transaksi->load('items');

        return Pdf::loadView('kasir.receipt_pdf', compact('transaksi'))
            ->download($transaksi->reference . '.pdf');
    }

    public function exportHistoryPdf(Request $request)
    {
        $filters = $this->validatedHistoryFilters($request);
        $transaksis = $this->historyQuery($filters)->get();
        $totalPenjualan = $transaksis->sum('total');

        return Pdf::loadView('kasir.history_pdf', compact('transaksis', 'filters', 'totalPenjualan'))
            ->download('riwayat-transaksi-kasir-' . now()->format('Y-m-d') . '.pdf');
    }

    private function validatedHistoryFilters(Request $request): array
    {
        return $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'q' => ['nullable', 'string', 'max:100'],
        ], [
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
        ]);
    }

    private function historyQuery(array $filters)
    {
        return KasirTransaksi::query()
            ->with('items')
            ->when($filters['tanggal_awal'] ?? null, fn ($query, $date) => $query->whereDate('completed_at', '>=', $date))
            ->when($filters['tanggal_akhir'] ?? null, fn ($query, $date) => $query->whereDate('completed_at', '<=', $date))
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', '%' . $search . '%')
                        ->orWhereHas('items', fn ($items) => $items->where('item_name', 'like', '%' . $search . '%'));
                });
            })
            ->orderByDesc('completed_at')
            ->orderByDesc('id');
    }
}
